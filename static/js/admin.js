/*
 * The MIT License
 *
 * Copyright 2022 Ivan Smitka <ivan at stimulus dot cz>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 *
 */

var $ = jQuery;
var PaymentQRCodeGeneratorAdmin;
PaymentQRCodeGeneratorAdmin = {
    init: function () {
        var wrapper = $(".payment-qr-code-generator");
        $("input", wrapper).change(function (event) {
            var indexRow = $(event.currentTarget).closest("[data-index]");
            var index = parseInt(indexRow.data("index"));
            const data = {
                index: index,
                name: $(event.currentTarget).attr("name"),
                value: $(event.currentTarget).val()
            };
            $.ajax("/wp-content/plugins/payment-qr-code-generator/api.php", {
                type: "post",
                dataType: "json",
                data: data,
                success: function (response, status, xhr) {
                    // Handle response from server
                    if (response.success) {
                        if (indexRow.attr("data-new")) {
                            var newRow = indexRow.clone();
                            newRow.attr("data-index", response.data.index + 1);
                            $("input", newRow).val("");
                            indexRow.removeAttr("data-new");
                            $("tbody", wrapper).append(newRow);
                        }
                    } else {
                        alert("Chyba při ukládání účtu");
                    }
                },
                error: function (xhr, status, error) {
                }
            });
        });
        wrapper.on("click", "a", function (event) {
            var link = $(event.currentTarget);
            var index = link.closest("[data-index]").data("index");
            const data = {
                index: index,
                remove: true
            };
            $.ajax("/wp-content/plugins/payment-qr-code-generator/api.php", {
                type: "post",
                dataType: "json",
                data: data,
                success: function (response, status, xhr) {
                    // Handle response from server
                    if (response.success) {
                        window.location.reload();
                    } else {
                        alert("Chyba při mazání účtu");
                    }
                }
            });
        });
    }
};
$(document).ready(function () {
    PaymentQRCodeGeneratorAdmin.init();
});
