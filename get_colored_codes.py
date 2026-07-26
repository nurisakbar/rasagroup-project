import openpyxl
import json
import sys

wb = openpyxl.load_workbook('/Applications/MAMP/htdocs/rasagroup/rasa_jubelio_prod_productss-new(2).xlsx', data_only=True)
ws = wb.active

codes = []
color_stats = {}

# We need to find the headers first to know exact columns, but based on output, Col A=0, Col B=1
for row in ws.iter_rows(min_row=3, max_col=2):
    code_cell = row[0]
    name_cell = row[1]
    
    if code_cell.value:
        fill = name_cell.fill
        color = None
        if fill and fill.start_color:
            color = fill.start_color.index
            if hasattr(color, 'rgb'):
                color = color.rgb
        elif fill and fill.fgColor:
            color = fill.fgColor.rgb
            
        color_str = str(color)
        if color_str not in color_stats:
            color_stats[color_str] = 0
        color_stats[color_str] += 1
        
        # Usually '00000000' or None means no fill
        if color_str not in ['00000000', 'None', 'FFFFFFFF', '0']:
            codes.append(str(code_cell.value))

with open('active_codes.json', 'w') as f:
    json.dump(codes, f)

print(f"Color stats: {color_stats}")
print(f"Found {len(codes)} colored codes.")
