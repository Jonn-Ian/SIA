import pandas as pd
from sqlalchemy import create_engine
from statsmodels.tsa.arima.model import ARIMA
import json

# Database connection details
engine = create_engine('mysql+pymysql://root:@localhost/detour_cafe')

# Query to get the data
query = "SELECT date_time, category, items_sold FROM db_sales ORDER BY date_time"

# Load data into a DataFrame
df = pd.read_sql(query, engine)

# Ensure 'date_time' is in datetime format and set it as the index
df['date_time'] = pd.to_datetime(df['date_time'])
df.set_index('date_time', inplace=True)

# Initialize an empty list to store forecast results
forecast_results = []

# Unique categories
categories = df['category'].unique()

# Process each category separately
for category in categories:
    # Filter data for the current category
    category_df = df[df['category'] == category]

    # Resample data to monthly frequency and aggregate items sold
    df_monthly = category_df.resample('M').sum()

    # Fit ARIMA model
    model = ARIMA(df_monthly['items_sold'], order=(5,1,0))  # Adjust order based on AIC/BIC criteria
    model_fit = model.fit()

    # Forecast future values
    forecast_steps = 12  # Number of months to forecast
    forecast = model_fit.forecast(steps=forecast_steps)
    forecast_dates = pd.date_range(start=df_monthly.index[-1] + pd.DateOffset(months=1), periods=forecast_steps, freq='M')
    
    # Create DataFrame for forecasted data
    forecast_df = pd.DataFrame({
        'date_time': forecast_dates,
        'category': category,
        'forecast_items_sold': forecast
    })
    forecast_results.append(forecast_df)

# Combine all category forecasts into a single DataFrame
forecast_df_combined = pd.concat(forecast_results)

# Save forecast data to JSON
forecast_df_combined.reset_index(drop=True, inplace=True)
forecast_json = forecast_df_combined.to_json(orient='records')

json_file_path = 'C:\\xampp\\htdocs\\detourcafe\\templates\\dashboard\\forecast_demand.json'
with open(json_file_path, 'w') as file:
    file.write(forecast_json)

print("Forecast data saved to forecast_demand.json")
