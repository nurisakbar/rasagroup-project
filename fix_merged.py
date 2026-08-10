import openpyxl
wb = openpyxl.load_workbook('/Applications/MAMP/htdocs/rasagroup/Faspay VA - Skenario Functional Test_V.3.0 static(2).xlsx')
sheet = wb['Transfer VA']
for range_ in sheet.merged_cells.ranges:
    print(range_)
