import pandas as pd
from sqlalchemy import create_engine
from statsmodels.tsa.arima.model import ARIMA
import json

# Step 1: Load data from MySQL into a Pandas DataFrame
engine = create_engine("mysql+pymysql://root:@localhost/detour_cafe")
query = "SELECT date_time, net_sales, gross_profit FROM db_sales"
df = pd.read_sql(query, con=engine)

# Step 2: Prepare the data
df['date_time'] = pd.to_datetime(df['date_time'])
df.set_index('date_time', inplace=True)
monthly_data = df.resample('ME').sum()

# Step 3: Forecast using ARIMA
# Forecast net_sales
model_net_sales = ARIMA(monthly_data['net_sales'], order=(5, 1, 0))
model_net_sales_fit = model_net_sales.fit()
forecast_net_sales = model_net_sales_fit.forecast(steps=12)

# Forecast gross_profit
model_gross_profit = ARIMA(monthly_data['gross_profit'], order=(5, 1, 0))
model_gross_profit_fit = model_gross_profit.fit()
forecast_gross_profit = model_gross_profit_fit.forecast(steps=12)

# Create a DataFrame for the forecasted values
forecast_df = pd.DataFrame({
    'date_time': pd.date_range(start=monthly_data.index[-1], periods=13, freq='ME')[1:],
    'forecast_net_sales': forecast_net_sales,
    'forecast_gross_profit': forecast_gross_profit
})

# Convert DataFrame to JSON
forecast_json = forecast_df.to_json(orient='split', date_format='iso')

# Save the JSON to a file
json_file_path = 'C:\\xampp\\htdocs\\detourcafe\\templates\\dashboard\\forecast_data_sales.json'  # Adjust the path as needed
with open(json_file_path, 'w') as file:
    file.write(forecast_json)
