<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    class/actions_quickeditline.class.php
 * \ingroup quickeditline
 * \brief   This file is an example hook overload class file
 *          Put some comments here
 */

/**
 * Class Actionsquickeditline
 */
class Actionsquickeditline
{
    /**
     * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
     */
    public $results = array();

    /**
     * @var string String displayed by executeHook() immediately after return
     */
    public $resprints;

    /**
     * @var array Errors
     */
    public $errors = array();

    /**
     * @var DoliDB $db
     */
    public $db;

    /**
     * @var Propal|Commande|Facture|SupplierProposal|CommandeFournisseur|FactureFournisseur
     */
    public $currentObject;

    /**
     * Constructor
     * @param DoliDB $db DB connector
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    public function doActions($parameters, &$object, &$action, $hookmanager)
    {
        $this->currentObject = &$object;
    }

    /**
     * @param array $parameters array of parameters
     * @param Propal|Commande|Facture|SupplierProposal|CommandeFournisseur|FactureFournisseur $object object
     * @param string $action action
     * @param HookManager $hookmanager object
     * @return void
     */
    public function printCommonFooter($parameters, &$object, &$action, $hookmanager)
    {
        global $conf;
        $clientContexts =  array('propalcard', 'ordercard', 'invoicecard');
        $supplierContexts = array('ordersuppliercard', 'invoicesuppliercard', 'supplier_proposalcard');
        $enabledContexts = array_merge($clientContexts, $supplierContexts);

        if (in_array($parameters['currentcontext'], $enabledContexts)) {
            ?>
            <style type="text/css">
                #tablelines tr[id^=row-] .linecoldescription:hover,
                #tablelines tr[id^=row-] .linecolvat:hover,
                #tablelines tr[id^=row-] .linecoluht:hover,
                #tablelines tr[id^=row-] .linecolqty:hover,
                #tablelines tr[id^=row-] .linecoldiscount:hover,
                #tablelines tr[id^=row-] .linecolmargin1:hover,
                #tablelines tr[id^=row-] .linecolmargin2:hover {
                    cursor: pointer;
                    text-decoration: underline;
                }
            </style>
            <script type="text/javascript">
                $(function () {
                    // Gestion de l'auto focus des éléments
                    var focus = function (params) {
                        if (typeof params.id !== 'undefined' && params.id.length > 0)
                        {
                            if (params.element === 'linecoldescription' && typeof CKEDITOR === 'object')
                            {
                                CKEDITOR.on("instanceReady",function() {
                                    CKEDITOR.instances[params.id].focus();
                                });
                            }
                            else if (params.element === 'linecolvat') $('#'+params.id).focus();
                            else $('#'+params.id).select();
                        }
                        else if (typeof params.name !== 'undefined' && params.name.length > 0)
                        {
                            $('input[name='+params.name+']').select();
                        }
                    };

                    $(document).on('click', '#tablelines tr[id^=row-] .linecoldescription', function (ev) {
                        // Ici je veux que ce soit uniquement le td pour ne pas entrer en conflit avec le module Nomenclature
                        if ($(ev.target).hasClass('linecoldescription')) $(this).siblings('.linecoledit').children('a').trigger('click', [focus, {element: 'linecoldescription', id: 'product_desc'}]);
                    });
                    $(document).on('click', '#tablelines tr[id^=row-] .linecolvat', function (ev) {
                        $(this).siblings('.linecoledit').children('a').trigger('click', [focus, {element: 'linecolvat', id: 'tva_tx'}]);
                    });
                    $(document).on('click', '#tablelines tr[id^=row-] .linecoluht', function (ev) {
                        $(this).siblings('.linecoledit').children('a').trigger('click', [focus, {element: 'linecoluht', id: 'price_ht'}])
                    });
                    $(document).on('click', '#tablelines tr[id^=row-] .linecolqty', function (ev) {
                        $(this).siblings('.linecoledit').children('a').trigger('click', [focus, {element: 'linecolqty', id: 'qty'}])
                    });
                    $(document).on('click', '#tablelines tr[id^=row-] .linecoldiscount', function (ev) {
                        $(this).siblings('.linecoledit').children('a').trigger('click', [focus, {element: 'linecoldiscount', id: 'remise_percent'}])
                    });
                    $(document).on('click', '#tablelines tr[id^=row-] .linecolmargin1', function (ev) {
                        $(this).siblings('.linecoledit').children('a').trigger('click', [focus, {element: 'linecolmargin1', id: 'buying_price'}])
                    });
                    $(document).on('click', '#tablelines tr[id^=row-] .linecolmargin2', function (ev) {
                        $(this).siblings('.linecoledit').children('a').trigger('click', [focus, {element: 'linecolmargin2', name: 'np_marginRate'}])
                    });

                    let qlu_in_edition = false;
                    $(document).on('click', '#tablelines tr[id^=row-] td.linecoledit a', function (ev, callback, callback_params) {
                        ev.preventDefault();
                        // let self = this;
                        if (qlu_in_edition) return;
                        qlu_in_edition = true;

                        $.get(this.href, {}, function (response) {

                            let backupIdRight = $('#id-right').clone(true);

                            $('#id-right').replaceWith($(response).find('#id-right'));
                            // $('#savelinebutton, #cancellinebutton').attr('type', 'button'); // Pas de soumission de formulaire ;)

                            let submitForm = function (ev) {
                                ev.preventDefault();
                                for (let ckeName in CKEDITOR.instances) {
                                    if (CKEDITOR.instances.hasOwnProperty(ckeName)) {
                                        let ckeInstance = CKEDITOR.instances[ckeName];
                                        let textarea = $(ckeInstance.element.$);
                                        textarea.val(ckeInstance.getData());
                                        console.log(ckeName, textarea[0], ckeInstance.getData(), textarea[0].name);
                                    }
                                }
                                let submitData = $(this).serializeArray();
                                if (ev.originalEvent.explicitOriginalTarget.name === 'cancel') {
                                    return cancelSubmit(ev);
                                }

                                submitData.push({name: 'save', value: $('#savelinebutton').val()});

                                $.post($('#addproduct').attr('action'), submitData, function (responsePost) {
                                    $('#id-right').replaceWith($(responsePost).find('#id-right'));
                                });

                                finalize();
                            };

                            let cancelSubmit = function (ev) {
                                $('#id-right').replaceWith(backupIdRight);
                                finalize();
                            };

                            let finalize = function () {
                                // une fois que l’on a validé ou annulé une édition de ligne, on remet tout au propre
                                qlu_in_edition = false;
                            };

                            $('#addproduct').submit(submitForm);
                            // $('#cancellinebutton').click(cancelEdit);

                            if (typeof callback === 'function')
                            {
                                callback(callback_params);
                            }

                        }, 'html');
                    });
                });
            </script>
            <?php
        }
    }
}
