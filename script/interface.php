<?php

if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL', 1);

	require('../config.php');
	dol_include_once('/comm/propal/class/propal.class.php');
	dol_include_once('/commande/class/commande.class.php');
	dol_include_once('/compta/facture/class/facture.class.php');
	dol_include_once('/supplier_proposal/class/supplier_proposal.class.php');
	dol_include_once('/fourn/class/fournisseur.commande.class.php');
	dol_include_once('/fourn/class/fournisseur.facture.class.php');

	$get = GETPOST('get');
	$objectid = GETPOST('objectid');
	$objectelement = GETPOST('objectelement');
	$lineid = GETPOST('lineid');

	switch ($get) {
        case 'view-line':
        case 'edit-line':

            global $mysoc;

            if ($get === 'view-line') $action = 'view';
            else $action = 'editline';

            $form = new Form($db);


            /** @var Propal|Commande|Facture $object */
            if ($objectelement == 'propal')
            {
                /** $_SERVER["PHP_SELF"] is override to keep link on deletion <a> */
                $_SERVER["PHP_SELF"] = dol_buildpath('comm/propal/card.php', 1);
                $object = new Propal($db);
            }
            elseif ($objectelement == 'commande')
            {
                $_SERVER["PHP_SELF"] = dol_buildpath('commande/card.php', 1);
                $object = new Commande($db);
            }
            elseif ($objectelement == 'facture')
            {
                $_SERVER["PHP_SELF"] = dol_buildpath('compta/facture/card.php', 1);
                $object = new Facture($db);
            }
            elseif ($objectelement == 'supplier_proposal')
            {
                $_SERVER["PHP_SELF"] = dol_buildpath('supplier_proposal/card.php', 1);
                $object = new SupplierProposal($db);
            }
            elseif ($objectelement == 'order_supplier')
            {
                $_SERVER["PHP_SELF"] = dol_buildpath('fourn/commande/card.php', 1);
                $object = new CommandeFournisseur($db);
            }
            elseif ($objectelement == 'invoice_supplier')
            {
                $_SERVER["PHP_SELF"] = dol_buildpath('fourn/facture/card.php', 1);
                $object = new FactureFournisseur($db);
            }

            $object->fetch($objectid);
            if (empty($object->thirdparty) && method_exists($object, 'fetch_thirdparty')) $object->fetch_thirdparty();

            $extrafieldsline = new ExtraFields($db);
            $extralabelsline = $extrafieldsline->fetch_name_optionals_label($object->table_element_line);

            foreach ($object->lines as $i => $line)
            {
                if ($line->id == $lineid)
                {
                    $object->printObjectLine($action, $line, true, count($object->lines), $i, 1, $mysoc, $object->thirdparty, $lineid, $extrafieldsline, '/core/tpl');
                    break;
                }
            }

            if ($action == 'view')
            {
                $urltorefreshaftermove = GETPOST('php_self');
                require DOL_DOCUMENT_ROOT.'/core/tpl/ajaxrow.tpl.php';
            }

            break;
	}
