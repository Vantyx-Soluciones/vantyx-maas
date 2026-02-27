<?php
/**
 * Vantyx MaaS Connector - Setup Page
 */

require '../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';

$langs->load("admin");
$langs->load("vantyxmaasconnector@vantyxmaasconnector");

if (!$user->admin)
    accessforbidden();

$action = GETPOST('action', 'aZ09');

if ($action == 'update') {
    dolibarr_set_const($db, 'VANTYXMAAS_TOKEN', GETPOST('VANTYXMAAS_TOKEN', 'alpha'), 'chaine', 0, '', $conf->entity);
    setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
}

/*
 * View
 */
llxHeader('', $langs->trans("Vantyx MaaS - Configuración"));

$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php?restore_lastsearch_values=1">' . $langs->trans("BackToModuleList") . '</a>';
print load_fiche_titre($langs->trans("Vantyx MaaS - Configuración"), $linkback, 'title_setup');

print '<form method="post" action="' . $_SERVER["PHP_SELF"] . '">';
print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
print '<input type="hidden" name="action" value="update">';

print dol_get_fiche_head([], '', '', -1);

print '<table class="noborder centertable" width="100%">';
print '<tr class="liste_titre">';
print '<td>' . $langs->trans("Parámetro") . '</td>';
print '<td>' . $langs->trans("Valor") . '</td>';
print '</tr>';

// Token de Vantyx
print '<tr class="oddeven">';
print '<td>Vantyx Cloud Token</td>';
print '<td><input type="text" name="VANTYXMAAS_TOKEN" size="40" value="' . $conf->global->VANTYXMAAS_TOKEN . '"></td>';
print '</tr>';

print '</table>';

print dol_get_fiche_end();

print '<div class="center"><input type="submit" class="button" value="' . $langs->trans("Save") . '"></div>';
print '</form>';

// Ayuda sobre Webhooks
print '<br>';
print '<div class="info">';
print '<strong>' . $langs->trans("Instrucciones de Conectividad") . ':</strong><br>';
print 'Para habilitar la Facturación Electrónica vía Vantyx Cloud, configure un Webhook nativo en Dolibarr:<br>';
print '1. Vaya a <strong>Configuración -> Módulos -> Webhooks</strong>.<br>';
print '2. Cree un nuevo Webhook para el evento <code>FACTURA_VALIDATE</code>.<br>';
print '3. URL de destino: <code>https://api.vantyx.net/index.php?token=' . ($conf->global->VANTYXMAAS_TOKEN ?: 'SU_TOKEN') . '</code>';
print '</div>';

llxFooter();
$db->close();
