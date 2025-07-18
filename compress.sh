sass scss/default.scss static/css/default.css
rm -rf lib/phpqrcode/cache/*
(cd ../ && zip -r payment-qr-code-generator/payment-qr-code-generator.zip \
  payment-qr-code-generator/data/data.json.template \
  payment-qr-code-generator/lib \
  payment-qr-code-generator/static \
  payment-qr-code-generator/LICENSE \
  payment-qr-code-generator/README.md \
  payment-qr-code-generator/api.php \
  payment-qr-code-generator/generator.php \
  payment-qr-code-generator/payment-qr-code-generator.php \
  )