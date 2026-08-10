with open('/Applications/MAMP/htdocs/rasagroup/rasagroup-project/parsed_docs.txt', 'r') as f:
    lines = f.readlines()
    for line in lines:
        if 'Notification Virtual Account SNAP Static' in line:
            print(line[:5000])
