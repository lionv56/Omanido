# XSS klaar

## Probleem

De transactiepagina was kwetsbaar voor Cross-site scripting.

In de database stond een transactieomschrijving met JavaScript-code:

```html
<script>alert("Je bent gehacked")</script>