<?php

require(__DIR__ . '/../../config.php');

require_login();
global $DB;
require_capability('moodle/site:config', context_system::instance());

$context = context_system::instance();

$PAGE->set_url(new moodle_url('/local/lmsdashboard/index.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_lmsdashboard'));
$PAGE->set_heading(get_string('pluginname', 'local_lmsdashboard'));
$PAGE->requires->css('/local/lmsdashboard/styles/styles.css');

$courses = get_courses('all', 'c.sortorder ASC', 'c.id, c.fullname, c.shortname, c.visible');
$visiblecourses = 0;
$hiddencourses = 0;

foreach ($courses as $course) {
    if ($course->id == SITEID) {
        continue;
    }

    if ($course->visible) {
        $visiblecourses++;
    } else {
        $hiddencourses++;
    }
}

echo $OUTPUT->header();

echo $OUTPUT->heading('Panel de cursos');
echo $OUTPUT->box(
    html_writer::tag('strong', 'Cursos visibles: ') . $visiblecourses .
    html_writer::tag('br', '') .
    html_writer::tag('strong', 'Cursos ocultos: ') . $hiddencourses,
    'generalbox'
);

if (empty($courses)) {
    echo $OUTPUT->notification(
        'No hay cursos disponibles.',
        'info'
    );
} else {
    $table = new html_table();
    $table->head = ['ID', 'Nombre del curso', 'Nombre corto', 'Estado', 'Estudiantes'];
    $table->attributes['class'] = 'generaltable lmsdashboard-table';

    foreach ($courses as $course) {
        if ($course->id == SITEID) {
            continue;
        }

        $status = $course->visible ? 'Visible' : 'Oculto';

        $courselink = html_writer::link(
            new moodle_url('/course/view.php', ['id' => $course->id]),
            format_string($course->fullname)
        );

        $studentrole = $DB->get_field(
            'role',
            'id',
            ['shortname' => 'student'],
            MUST_EXIST
        );

        $studentcount = count_role_users(
            $studentrole,
            context_course::instance($course->id)
        );

        $table->data[] = [
            $course->id,
            $courselink,
            s($course->shortname),
            $status,
            $studentcount
        ];
    }

    if (!empty($table->data)) {
        echo html_writer::table($table);
    } else {
        echo $OUTPUT->notification(
            'No hay cursos para mostrar.',
            'info'
        );
    }
}

echo $OUTPUT->footer();
