<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015-2019 ATM Consulting <support@atm-consulting.fr>
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
        global $object;
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
                    var quickeditline_focus = function (params) {
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

                    var template_create = $('#tablelines tr.liste_titre_create').clone(true);
                    var template_create_extra = $('#tablelines tr.liste_titre_create').nextAll('tr').clone(true);

                    if (typeof CKEDITOR !== "undefined" && CKEDITOR.instances.dp_desc)
                    {
                        var quickeditline_ckeditor_config = CKEDITOR.instances.dp_desc.config;
                    }

                    var quickeditline_hide_create_template = function() {
                        if (typeof CKEDITOR !== "undefined") CKEDITOR.instances.dp_desc.destroy();
                        $('#tablelines tr.liste_titre_create').nextAll('tr').remove();
                        $('#tablelines tr.liste_titre_create').remove();
                        $('.tabsAction').hide();
                    };
                    var quickeditline_show_create_template = function() {
                        $('#tablelines').append($(template_create));
                        $('#tablelines').append($(template_create_extra));
                        if (typeof CKEDITOR !== "undefined") CKEDITOR.replace('dp_desc', quickeditline_ckeditor_config);
                        $('.tabsAction').show();
                    };

                    var quickeditline_callback_focus_params = null;

                    $(document).on('click', '#tablelines tr[id^=row-] .linecoldescription', function (ev) {
                        // Ici je veux que ce soit uniquement le td pour ne pas entrer en conflit avec le module Nomenclature
                        if ($(ev.target).hasClass('linecoldescription')) $(this).siblings('.linecoledit').children('a').not('[name*="duplicate"]').trigger('click');
                        quickeditline_callback_focus_params = {element: 'linecoldescription', id: 'product_desc'};
                    });
                    $(document).on('click', '#tablelines tr[id^=row-] .linecolvat', function (ev) {
                        $(this).siblings('.linecoledit').children('a').not('[name*="duplicate"]').trigger('click');
                        quickeditline_callback_focus_params = {element: 'linecolvat', id: 'tva_tx'};
                    });
                    $(document).on('click', '#tablelines tr[id^=row-] .linecoluht', function (ev) {
                        $(this).siblings('.linecoledit').children('a').not('[name*="duplicate"]').trigger('click');
                        quickeditline_callback_focus_params = {element: 'linecoluht', id: 'price_ht'};
                    });
                    $(document).on('click', '#tablelines tr[id^=row-] .linecolqty', function (ev) {
                        $(this).siblings('.linecoledit').children('a').not('[name*="duplicate"]').trigger('click');
                        quickeditline_callback_focus_params = {element: 'linecolqty', id: 'qty'};
                    });
                    $(document).on('click', '#tablelines tr[id^=row-] .linecoldiscount', function (ev) {
                        $(this).siblings('.linecoledit').children('a').not('[name*="duplicate"]').trigger('click');
                        quickeditline_callback_focus_params = {element: 'linecoldiscount', id: 'remise_percent'};
                    });
                    $(document).on('click', '#tablelines tr[id^=row-] .linecolmargin1', function (ev) {
                        $(this).siblings('.linecoledit').children('a').not('[name*="duplicate"]').trigger('click');
                        quickeditline_callback_focus_params = {element: 'linecolmargin1', id: 'buying_price'};
                    });
                    $(document).on('click', '#tablelines tr[id^=row-] .linecolmargin2', function (ev) {
                        $(this).siblings('.linecoledit').children('a').not('[name*="duplicate"]').trigger('click');
                        quickeditline_callback_focus_params = {element: 'linecolmargin2', name: 'np_marginRate'};
                    });

                    let ajaxViewLine = function (ev) {
                        $.ajax({
                            url: "<?php echo dol_buildpath('quickeditline/script/interface.php', 1); ?>"
                            ,type: 'POST'
                            ,dataType: 'html'
                            ,data: {
                                get: 'view-line'
                                ,objectid: <?php echo $object->id; ?>
                                ,objectelement: "<?php echo $object->element; ?>"
                                ,lineid: $('#addproduct input[name=lineid]').val()
                                ,php_self: "<?php echo $_SERVER['PHP_SELF'].'?id='.$object->id; ?>"
                            }
                        }).done(function(html) {
                            // Remove extrafields lines
                            $('#savelinebutton').closest('tr').nextUntil('tr[id^=row-]', 'tr').remove();

                            quickeditline_show_create_template();

                            $('#savelinebutton').closest('tr').replaceWith(html);

                            $('#addproduct').find('input[name=action]').val('addline');

                            qlu_in_edition = false;
                        });

                    };

                    let submitForm = function (ev) {
                        ev.preventDefault();
                        for (let ckeName in CKEDITOR.instances) {
                            if (CKEDITOR.instances.hasOwnProperty(ckeName)) {
                                let ckeInstance = CKEDITOR.instances[ckeName];
                                let textarea = $(ckeInstance.element.$);
                                textarea.val(ckeInstance.getData());
                                // console.log(ckeName, textarea[0], ckeInstance.getData(), textarea[0].name);
                            }
                        }

                        let data = {};

                        let submitData = $(this).serializeArray();
                        for (let i in submitData) {
                            data[submitData[i].name] = submitData[i].value;
                        }

                        if ($(ev.originalEvent).find('input[type=submit][clicked=true]').attr('name') === 'cancel') {
                            return ajaxViewLine(ev);
                        }

                        data.save = $('#savelinebutton').val()

                        $.ajax({
                            url: $('#addproduct').attr('action')
                            ,type: 'POST'
                            ,data: data
                        }).done(function() {
                            ajaxViewLine(ev);
                        });
                    };

                    let qlu_in_edition = false;
                    $(document).on('click', '#tablelines tr[id^=row-] td.linecoledit a', function (ev, callback, callback_params) {
                        ev.preventDefault();
                        // let self = this;
                        if (qlu_in_edition) return;
                        qlu_in_edition = true;

                        let urlParams = new URLSearchParams(this.search);
                        let lineid = urlParams.get('lineid');

                        $.ajax({
                            url: "<?php echo dol_buildpath('quickeditline/script/interface.php', 1); ?>"
                            ,type: 'POST'
                            ,dataType: 'html'
                            ,data: {
                                get: 'edit-line'
                                ,objectid: <?php echo $object->id; ?>
                                ,objectelement: "<?php echo $object->element; ?>"
                                ,lineid: lineid
                            }
                        }).done(function(html) {
                            // Remove extrafields lines
                            $('#row-'+lineid).nextUntil('tr[id^=row-]', 'tr').remove();

                            quickeditline_hide_create_template();

                            $('#row-'+lineid).replaceWith(html);

                            if (quickeditline_callback_focus_params) quickeditline_focus(quickeditline_callback_focus_params);

                            $('#addproduct').find('input[name=action]').val('updateline');


                        });
                    });

					$('#addproduct').on('submit', submitForm);
                });
            </script>
            <?php
        }
    }
}
