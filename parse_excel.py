import openpyxl
wb = openpyxl.load_workbook('/Users/nurisakbar/Downloads/Faspay VA - Skenario Functional Test_V.3.0 static(2).xlsx')
sheet = wb.active
for i, cell in enumerate(sheet[8]):
    print(f"Col {i+1}: {cell.value}")
