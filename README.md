# istedalis

ISTE Denizcilik Teknolojileri MYO dalis kayit arayuzu.

Bu surum Docker ve Laravel kullanmadan Render native Node runtime ile calisir. Uygulama mevcut HTML/CSS/JS arayuzunu Node'un yerlesik HTTP sunucusu ile servis eder.

## Yerel Calistirma

Node.js 20 veya uzeri gerekir.

```bash
npm start
```

Varsayilan adres:

```text
http://localhost:10000
```

Render veya baska bir platform `PORT` ortam degiskeni verirse uygulama o portu kullanir.

## Render Deploy

`render.yaml` Docker'siz Node servis olarak ayarlidir:

- Runtime: `node`
- Build command: `npm install`
- Start command: `npm start`
- Health check: `/up`

Bu surum dis npm paketi kullanmaz. `npm install` yalnizca Render'in standart Node build adimini tamamlamasi icindir.

## Veri Notu

Mevcut arayuz verileri tarayicinin `localStorage` alaninda tutar. Bu nedenle veriler merkezi sunucuda veya PostgreSQL'de saklanmaz. Cok kullanicili, kalici veritabani istenirse sonraki adim Node API + PostgreSQL gecisidir.
