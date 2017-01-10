from pandas_datareader import data as pdr
import datetime
import pandas as pd
import numpy as np



def download_data(ticker, start = datetime.datetime(1950, 1, 1),
                  end = datetime.datetime.today(),
                  source = 'yahoo', drop_extra = True):
    # may need to use this for weekly data
    # http://stackoverflow.com/a/20584971
    df = pdr.DataReader(ticker, source, start, end)
    df = df.rename(columns={'Adj Close':'adj_close'})
    df.index.name = 'date'
    if drop_extra:
        df = df[['adj_close']]
    return df

def get_returns(df, drop_extra = True):
    df.loc[:,'prior'] = df['adj_close'].shift(1)
    df = df.dropna()
    change = (df['prior'] / df['adj_close']) - 1
    df.loc[:,'returns'] = change
    if drop_extra:
        df = df[['returns']]
    return df

def get_beta(a, b):
    return np.cov(b, a)[0,1]/np.var(b)

def get_value(a, b, kind = 'beta'):
    # need to add in more calculation types (e.g., Std Dev, Correl, etc.)
    if kind=='beta':
        return get_beta(a, b)
    else:
        return None

def get_chart_data(tickers, market = '^GSPC', kind = 'beta',
                   start = datetime.datetime(1950, 1, 1),
                   end = datetime.datetime.today(), rolling_weeks = 156,
                   source = 'yahoo', return_type = 'df'):
    # download market data
    mkt = download_data(market, start, end, source, drop_extra=True)
    mkt = get_returns(mkt, drop_extra=True)
    mkt.columns = ['market']
    
    # download stock data for each ticker provided
    stocks = []
    min_date = end
    for ticker in tickers:
        df = download_data(ticker, start, end, source, drop_extra=True)
        df = get_returns(df, drop_extra=True)
        df.columns = [ticker]
        stocks.append(df.copy())
        
        # find min date across all stock data collected
        temp_date = df.index.min().to_pydatetime()
        min_date = min(min_date, temp_date)
    
    # truncate market data based on min_date found
    mkt = mkt.loc[mkt.index>=min_date]
    df = pd.concat([mkt] + stocks, axis=1)
    
    # prep dict for capturing calculations
    out = {}
    for ticker in tickers:
        out[ticker] = []
    
    # calc values
    rolling_start = min_date + datetime.timedelta(weeks=rolling_weeks)
    dates = list(df.ix[rolling_start:].index.to_pydatetime())
    for date in dates:
        prior_date = date - datetime.timedelta(weeks=rolling_weeks)
        tmp = df.ix[prior_date:date]
        for ticker in tickers:
            val = get_value(tmp[ticker], tmp['market'])
            out[ticker].append(val)
    
    d = {'data':out, 'dates':dates}
    if return_type=='dict':
        return d
    elif return_type=='df':
        return pd.DataFrame(d['data'], index=d['dates'])
    return d
