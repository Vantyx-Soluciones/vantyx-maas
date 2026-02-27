<?php
/**
 * Setup page for Vantyx MaaS Connector
 */

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/functions.lib.php';

$langs->load("admin");
$langs->load("vantyxmaasconnector@vantyxmaasconnector");

if (!$user->admin)
    accessforbidden();

$action = GETPOST('action', 'alpha');

// Acciones de guardado
if ($action == 'update') {
    $res1 = dolibarr_set_const($db, 'VANTYXMAAS_API_URL', GETPOST('VANTYXMAAS_API_URL', 'alpha'), 'chaine', 0, '', $conf->entity);
    $res2 = dolibarr_set_const($db, 'VANTYXMAAS_TOKEN', GETPOST('VANTYXMAAS_TOKEN', 'alpha'), 'chaine', 0, '', $conf->entity);
    $res3 = dolibarr_set_const($db, 'VANTYXMAAS_PRODUCTION', GETPOST('VANTYXMAAS_PRODUCTION', 'alpha'), 'chaine', 0, '', $conf->entity);

    if ($res1 > 0 || $res2 > 0 || $res3 > 0) {
        setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }
}

/*
 * View
 */
$form = new Form($db);

llxHeader('', $langs->trans("VantyxMaaSSetup"));

$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php?restore_lastsearch_values=1">' . $langs->trans("BackToModuleList") . '</a>';
print load_fiche_titre($langs->trans("VantyxMaaSSetup"), $linkback, 'title_setup');

print '<form action="' . $_SERVER["PHP_SELF"] . '" method="post">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="action" value="update">';

print '<table class="noborder centertable" width="100%">';
print '<tr class="liste_titre">';
print '<td>' . $langs->trans("Parameter") . '</td>';
print '<td>' . $langs->trans("Value") . '</td>';
print '</tr>';

// URL
print '<tr class="oddeven"><td>';
print $form->textwithpicto($langs->trans("VantyxMaaSURL"), $langs->trans("VantyxMaaSURLDesc"));
print '</td><td>';
print '<input type="text" size="60" name="VANTYXMAAS_API_URL" value="' . $conf->global->VANTYXMAAS_API_URL . '">';
print '</td></tr>';

// Token
print '<tr class="oddeven"><td>';
print $form->textwithpicto($langs->trans("VantyxMaaSToken"), $langs->trans("VantyxMaaSTokenDesc"));
print '</td><td>';
print '<input type="text" size="60" name="VANTYXMAAS_TOKEN" value="' . $conf->global->VANTYXMAAS_TOKEN . '">';
print '</td></tr>';

// Production
print '<tr class="oddeven"><td>';
print $langs->trans("VantyxMaaSProduction");
print '</td><td>';
print $form->selectyesno("VANTYXMAAS_PRODUCTION", $conf->global->VANTYXMAAS_PRODUCTION, 1);
print '</td></tr>';

print '</table>';

print '<div class="tabsAction">';
print '<input type="submit" class="button" value="' . $langs->trans("Modify") . '">';
print '</div>';

print '</form>';

llxFooter();
$db->close();
