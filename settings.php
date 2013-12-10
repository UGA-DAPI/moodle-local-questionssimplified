<?php
/**
 * @copyright  2013 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* @var $ADMIN admin_root */
/* @var $settings object */

if ($ADMIN->fulltree) {
    $s = new admin_setting_confightmleditor(
        'helppage',
        "Page d'aide",
        "Ce texte sera affiché sur une page d'aide, accessible depuis le menu global Mes questions.",
        '',
        PARAM_CLEANHTML
    );
    $s->plugin = 'local_questionssimplified';
    $settings->add($s);

    $s = new admin_setting_configtext(
        'cohortpattern',
        "Cohortes d'enseignants",
        "Le menu sera affiché seulement pour les membres des cohortes dont l'idnumber correspondra à ce motif, appliqué par un SQL LIKE.",
        '',
        PARAM_RAW
    );
    $s->plugin = 'local_questionssimplified';
    $settings->add($s);
}
