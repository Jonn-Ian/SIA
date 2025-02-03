import pandas as pd
from sqlalchemy import create_engine
import json
import numpy as np

# Step 1: Load data from MySQL into a Pandas DataFrame
engine = create_engine("mysql+pymysql://root:@localhost/detour_cafe")
query = "SELECT last_updated, item, category, cases, pack, kgs, pcs, gms, threshold FROM db_inventory"
df = pd.read_sql(query, con=engine)

# Step 2: Prepare the data
df['last_updated'] = pd.to_datetime(df['last_updated'])
df.set_index('last_updated', inplace=True)

# Convert all units to a common unit (e.g., kilograms)
conversion_factors = {
    'cases': 1,    # Define conversion factor for cases to kg
    'pack': 0.5,   # Define conversion factor for pack to kg
    'kgs': 1,
    'pcs': 0.01,   # Define conversion factor for pcs to kg
    'gms': 0.001   # Convert grams to kilograms
}
df['total_units'] = df[['cases', 'pack', 'kgs', 'pcs', 'gms']].apply(
    lambda x: np.dot(x, [conversion_factors[col] for col in ['cases', 'pack', 'kgs', 'pcs', 'gms']]), axis=1
)

# Resample data by week-end
df = df.groupby(['category', 'item']).resample('W').sum()

# Step 3: Calculate total inventory and forecast future levels
forecast_results = []

# Iterate over each category
for category in df.index.levels[0]:
    category_data = df.loc[category]

    print(f"Processing category: {category}")
    print(f"Available items in category '{category}': {category_data.index.levels[0]}")

    # Iterate over each item within the category
    for item in category_data.index.levels[0]:
        try:
            item_data = category_data.loc[item].dropna()
            if len(item_data) == 0:
                print(f"No data available for item: {item} in category: {category}. Skipping.")
                continue

            # Check for minimum data points
            if len(item_data) < 2:
                print(f"Not enough data for item: {item} in category: {category}. Using available data.")
                item_data['sma'] = item_data['total_units']
                window_size = 1
            else:
                window_size = min(3, len(item_data))
                item_data['sma'] = item_data['total_units'].rolling(window=window_size, min_periods=1).mean()

            # Forecast future total units using SMA
            forecast_index = pd.date_range(start=item_data.index[-1] + pd.DateOffset(weeks=1), periods=8, freq='W')
            last_sma_value = item_data['sma'].iloc[-1] if len(item_data) > 0 else 0
            forecast_units = [last_sma_value] * 8

            forecast_df = pd.DataFrame({
                'date_time': forecast_index,
                'forecast_units': forecast_units
            })

            forecast_results.append({
                'category': category,
                'item': item,
                'forecast': forecast_df.to_dict(orient='records')
            })

            print(f"Forecast DataFrame for item {item} in category {category}:\n{forecast_df}")

        except KeyError as e:
            print(f"KeyError: {e} for item in category {category}. It might not exist in the data.")
            continue

# Step 4: Convert forecast results to JSON and save to file
forecast_json = json.dumps(forecast_results, default=str)

json_file_path = 'C:\\xampp\\htdocs\\detourcafe\\templates\\dashboard\\forecast_levels.json'
with open(json_file_path, 'w') as file:
    file.write(forecast_json)

# Display the results
forecast_results_df = pd.DataFrame(forecast_results)
print(f"Forecast Results DataFrame:\n{forecast_results_df}")
