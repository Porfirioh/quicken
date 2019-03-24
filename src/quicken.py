from lxml import html
import requests
import time
import datetime

ft_format = '%A, %B %d, %Y';

symbols = {
    'EME': 'GB0007906794:GBX',
    'EQU': 'GB0007828709:GBX',
    'PAC': 'GB0007801532:GBX',
    'GBL': 'GB0007906687:GBX',
    'PP2': 'GB00B09CD637:GBX',
    'MAN': 'GB00B1VNF546:GBX',
    'GB00BYW6SY38': 'GB00BYW6SY38:GBX',
    'GB00BYNYY264': 'GB00BYNYY264:GBX',
    'GB00BV9FRD45': 'GB00BV9FRD45:GBX',
    'GB00BV9FRG75': 'GB00BV9FRG75:GBX',
    'GB00BYM58175': 'GB00BYM58175:GBX',
    'GB00BD6DNV57': 'GB00BD6DNV57:GBX'
}

now = datetime.datetime.now()
print("E\:{0}.txt".format(now.strftime("%Y-%m-%d")))

filename = "/Users/simonlewis/Code/quicken/prices/{0}.txt".format(now.strftime("%Y-%m-%d"))
price_file = open(filename, 'w')

for key, symbol in symbols.items():
    query = "s={0}".format(symbol)
    url = 'https://markets.ft.com/data/funds/tearsheet/historical?{0}'.format(query)

    page = requests.get(url)
    tree = html.fromstring(page.content)

    classname="mod-tearsheet-overview__quote__bar"
    quote_bar = tree.xpath("//*[contains(@class, '{0}')]".format(classname))

    for node in quote_bar:
        li = node.getchildren()
        span = li[0].getchildren()
        price_unit = span[0].text.replace('Price (', '').replace(')', '').strip()

    tables = tree.xpath("//tbody")

    for rows in tables:
        data = {}
        for cells in rows:
            data['date'] = time.strptime(cells[0].getchildren()[0].text, ft_format)

            price = float(cells[1].text.replace(',', ''))
            data['price'] = '{0:.2f}'.format(price)
            if price_unit == 'GBX':
                price = price / 100
                data['price'] = '{0:.4f}'.format(price)

            price_row = "{0}, {1}, {2}\r\n".format(key, data['price'], time.strftime("%d/%m/%y", data['date']))
            price_file.write(price_row)

price_file.close()

print('Done')
