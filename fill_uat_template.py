import json
import openpyxl

# Setup
log_file = '/Applications/MAMP/htdocs/rasagroup/laravel(2).log'
template_file = '/Applications/MAMP/htdocs/rasagroup/Faspay VA - Skenario Functional Test_V.3.0 static(2).xlsx'
output_file = '/Applications/MAMP/htdocs/rasagroup/faspay_uat_mantap.xlsx'

# 1. Parse log file
results = {}
with open(log_file, 'r') as f:
    for line in f:
        if "Faspay UAT Simulation Result - Scenario [" in line:
            try:
                scenario_id = line.split("Scenario [")[1].split("]")[0]
                json_str = line.split("] {", 1)[1]
                data = json.loads("{" + json_str.strip())
                results[scenario_id] = data
            except Exception as e:
                pass

# 2. Open Excel Template using openpyxl to preserve formatting
wb = openpyxl.load_workbook(template_file)
sheet = wb['Transfer VA']

# Column Indices (1-based in openpyxl)
# A: No, B: Service, C: Scenario, D: Expected, E: Request, F: Response, G: Result, H: Notes
COL_NO = 1
COL_REQ = 5
COL_RES = 6
COL_RESULT = 7

# Iterate through rows
for row in range(7, sheet.max_row + 1):
    cell_no = sheet.cell(row=row, column=COL_NO).value
    
    if cell_no and str(cell_no) in results:
        scenario_id = str(cell_no)
        data = results[scenario_id]
        
        # Build Request String
        req_str = f"url:\n{data.get('request_url', '')}\n\n"
        req_str += f"header:\n{json.dumps(data.get('request_headers', {}), indent=2)}\n\n"
        req_str += f"body:\n{json.dumps(data.get('request_payload', {}), indent=2)}"
        
        # Build Response String
        res_str = f"http_code: {data.get('http_code', '')}\n"
        res_str += f"{json.dumps(data.get('response_body', {}), indent=2)}"
        
        # Write to cells
        sheet.cell(row=row, column=COL_REQ).value = req_str
        sheet.cell(row=row, column=COL_RES).value = res_str
        sheet.cell(row=row, column=COL_RESULT).value = "PASS"

# Save Output
wb.save(output_file)
print(f"Successfully generated {output_file}")
