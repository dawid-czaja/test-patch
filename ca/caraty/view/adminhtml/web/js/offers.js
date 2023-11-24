require([
        'jquery',
        'mage/translate'],
    function($){
        $( document ).ready(function () {
            var offerHTML = '<div id=\"caraty_offer_row__id_\" class=\"caraty-offer-row\" data-offer-index="_id_"> ' +
                '<div>' +
                '<label for=\"CARATY_OFFER_ID__id_\"><strong>Oferta [_id_]</strong></label> ' +
                '<br>' +
                '</div>' +
                '<div> ' +
                '<div> ' +
                '<div class=\"caraty-short\"><span>Identyfikator:</span></div>' +
                '<div class=\"caraty-long\"> ' +
                '<input id=\"CARATY_OFFER_ID__id_\" type=\"text\" size=\"20\" name=\"CARATY_OFFER_ID__id_\" value=\"_offerid_\"/> ' +
                '</div><br><br>' +
                '<div class=\"caraty-short\"><span>Kategorie:</span></div>' +
                '<div class=\"caraty-long\"> ' +
                '<input id=\"CARATY_OFFER_CATEGORY__id_\" type=\"text\" size=\"20\" name=\"CARATY_OFFER_CATEGORY__id_\" value=\"_offercategory_\"/> ' +
                //'<select class=\"caraty-category-select\" name=\"CARATY_OFFER_CATEGORY__id_[]\" id=\"CARATY_OFFER_CATEGORY__id_\" multiple=\"multiple\"> </select>' +
                '</div><br><br>' +
                '<div class=\"caraty-short\"><span>Priorytet:</span></div>' +
                '<div class=\"caraty-long\">' +
                '<select name=\"CARATY_OFFER_PRIORITY__id_\" id=\"CARATY_OFFER_PRIORITY__id_\">' +
                '<option value=\"1\">1</option>' +
                '<option value=\"2\">2</option>' +
                '<option value=\"3\">3</option>' +
                '<option value=\"4\">4</option>' +
                '<option value=\"5\">5</option>' +
                '</select>' +
                '</div><br><br>' +
                '<button type=\"button\" class=\"button button-primary caraty-deletebutton__id_\" onclick=\"jQuery(this).closest(\'.caraty-offer-row\').remove()\">Usuń</button>' +
                '</div>' +
                '</div>' +
                '</div>';

            var containerHTML =
                '<tr valign="top">\n' +
                '<td class="label"><label for="payment_us_ca_raty_default_priority"><span>Identyfikatory ofert</span></label></td>\n' +
                '<td>\n' +
                '<button class="button button-secondary" type="button" onclick="jQuery(\'#caraty_offers_container_box\').toggle();">Pokaż/Ukryj listę</button>\n' +
                '</td>\n' +
                '</tr>' +
                '<tr valign="top">\n' +
                '<th scope="row"></th>\n' +
                '<td>\n' +
                '<style>.select2-container { width: 100% !important; } .caraty-offer-row {background: gainsboro;padding: 10px;margin: 10px 0 10px 0px;border-radius: 5px;} .caraty-short {display: inline-block;width: 25%;} .caraty-long {display: inline-block;width: 75%;} </style>\n' +
                '<div id="caraty_offers_container_box" style="display: none;">\n' +
                '<button id="caraty_add_new_offer" class="button button-primary" type="button">Dodaj nową ofertę</button>\n' +
                '<div id="caraty_offers_container"></div>\n' +
                '</div>\n' +
                '</td>\n' +
                '</tr>';

            // 1. hide offer field
            var offerField = $("input[id$='ca_raty_offer_id']");
            $("tr[id$='ca_raty_offer_id']").hide();

            // 2. insert container after
            $(containerHTML).insertAfter("tr[id$='ca_raty_default_priority']");

            // 3. get and process saved value
            var chosenVars = [];
            try {
                chosenVars = JSON.parse(offerField.val());
            } catch (e) {
                offerField.val('');
            }

            // 4. display all offers
            if (typeof chosenVars[0] !== 'undefined') {
                for (let i = 0; i < 50; i++) {
                    if (jQuery('#caraty_offer_row_' + i).length === 0 && typeof chosenVars[i] !== 'undefined') {
                        if (i === 0) { // insert at beginning
                            jQuery('#caraty_offers_container').prepend(offerHTML
                                .replaceAll('_id_', i)
                                .replace('_offerid_', chosenVars[i]['id'])
                                .replace('_offercategory_', chosenVars[i]['category'])
                                .replace('option value=\"' + chosenVars[i]['priority'] + '\"', 'option value=\"' + chosenVars[i]['priority'] + '\" selected')
                            );
                        } else {
                            jQuery(offerHTML
                                .replaceAll('_id_', i)
                                .replace('_offerid_', chosenVars[i]['id'])
                                .replace('_offercategory_', chosenVars[i]['category'])
                                .replace('option value=\"' + chosenVars[i]['priority'] + '\"', 'option value=\"' + chosenVars[i]['priority'] + '\" selected')
                            ).insertAfter('#caraty_offer_row_' + (i - 1));
                        }

                        var caratyRow = $('#caraty_offer_row_' + i);
                        caratyRow.find('#CARATY_OFFER_ID_' + i).on('change', function () {
                            saveOfferData();
                        });
                        caratyRow.find('#CARATY_OFFER_CATEGORY_' + i).on('change', function () {
                            saveOfferData();
                        });
                        caratyRow.find('#CARATY_OFFER_PRIORITY_' + i).on('change', function () {
                            saveOfferData();
                        });
                        $('.caraty-deletebutton_' + i).on('click', function () {
                            saveOfferData();
                        });
                    }
                }
            }

            // Update OfferID field with JSON
            function saveOfferData () {
                var offerData = [];
                $.each($('.caraty-offer-row'), function () {
                    offerData.push({
                        'id': $(this).find("input[id^='CARATY_OFFER_ID_']").val(),
                        'category': $(this).find("input[id^='CARATY_OFFER_CATEGORY_']").val(),
                        'priority': $(this).find("select[id^='CARATY_OFFER_PRIORITY_']").val(),
                    });
                });

                offerField.val(JSON.stringify(offerData));
            }

            // OfferID add new offer - button pressed
            $('#caraty_add_new_offer').on('click', function() {
                for (let i = 0; i < 50; i++) {
                    if ($('#caraty_offer_row_' + i).length === 0) {
                        if (i === 0) { // insert at beginning
                            $('#caraty_offers_container').prepend(offerHTML
                                .replaceAll('_id_', i)
                                .replace('_offerid_', '')
                                .replace('_offercategory_', '')
                            );
                        } else {
                            $(offerHTML
                                .replaceAll('_id_', i)
                                .replace('_offerid_', '')
                                .replace('_offercategory_', '')
                            ).insertAfter('#caraty_offer_row_' + (i - 1));
                        }

                        var caratyRow = $('#caraty_offer_row_' + i);
                        caratyRow.find('#CARATY_OFFER_ID_' + i).on('change', function () {
                            saveOfferData();
                        });
                        caratyRow.find('#CARATY_OFFER_CATEGORY_' + i).on('change', function () {
                            saveOfferData();
                        });
                        caratyRow.find('#CARATY_OFFER_PRIORITY_' + i).on('change', function () {
                            saveOfferData();
                        });
                        $('.caraty-deletebutton_' + i).on('click', function () {
                            saveOfferData();
                        });

                        break;
                    }
                }
            });
        });
    }
);
