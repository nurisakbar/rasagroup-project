import pandas as pd

file_path = '/Applications/MAMP/htdocs/rasagroup/UAT QRIS/FASPAY Direct Debit - Skenario Functional Test_V.3.2.xlsx'

try:
    # Try reading the first few sheets to find the scenarios
    xls = pd.ExcelFile(file_path)
    for sheet_name in xls.sheet_names:
        df = pd.read_excel(xls, sheet_name=sheet_name, header=None)
        # Search for 19.14, 19.21, 19.22 in the first column
        for index, row in df.iterrows():
            if str(row[0]).strip() in ['19.14', '19.21', '19.22']:
                print(f"--- Sheet: {sheet_name} | Scenario: {row[0]} ---")
                print(f"Service: {row[1]}")
                print(f"Scenario Desc: {row[2]}")
                print(f"Expected: {row[3]}")
                print(f"Request: {row[4]}")
                print(f"Response: {row[5]}")
except Exception as e:
    print(f"Error reading Excel: {e}")
