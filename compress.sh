#!/bin/bash
rm -rf lib/phpqrcode/cache/*
sass scss/default.scss static/css/default.css
VERSION=$(git tag -l)
echo "${VERSION}"
sed -i -E "s/^ \* Version\: .*$/ * Version: ${VERSION}/g" payment-qr-code-generator.php
(cd ../ && zip -r payment-qr-code-generator/${VERSION}.zip \
  payment-qr-code-generator/data/data.json.template \
  payment-qr-code-generator/lib \
  payment-qr-code-generator/static \
  payment-qr-code-generator/LICENSE \
  payment-qr-code-generator/README.md \
  payment-qr-code-generator/api.php \
  payment-qr-code-generator/generator.php \
  payment-qr-code-generator/payment-qr-code-generator.php \
  )