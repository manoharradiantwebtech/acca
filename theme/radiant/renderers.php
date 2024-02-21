<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Outputs the navigation tree.
 *
 * @since     Moodle 2.0
 * @package   block_navigation
 * @copyright 2009 Sam Hemelryk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Renderer for block navigation
 *
 * @package   block_navigation
 * @category  navigation
 * @copyright 2009 Sam Hemelryk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/blocks/navigation/renderer.php');
require_once ($CFG->dirroot . '/mod/quiz/renderer.php');
require_once ($CFG->dirroot . '/question/engine/renderer.php');
class theme_radiant_block_navigation_renderer extends block_navigation_renderer {
    /**
     * Produces a navigation node for the navigation tree
     *
     * @param navigation_node[] $items
     * @param array $attrs
     * @param int $expansionlimit
     * @param array $options
     * @param int $depth
     * @return string
     */
    protected function navigation_node($items, $attrs=array(), $expansionlimit=null, array $options = array(), $depth=1) {
        // Exit if empty, we don't want an empty ul element.
        if (count($items) === 0) {
            return '';
        }

        // Turn our navigation items into list items.
        $lis = array();
        // Set the number to be static for unique id's.
        static $number = 0;
        $htmlidprefix = html_writer::random_id();
        foreach ($items as $item) {
            $number++;
            if (!$item->display && !$item->contains_active_node()) {
                continue;
            }

            $isexpandable = (empty($expansionlimit) || ($item->type > navigation_node::TYPE_ACTIVITY || $item->type < $expansionlimit) || ($item->contains_active_node() && $item->children->count() > 0));

            // Skip elements which have no content and no action - no point in showing them
            if (!$isexpandable && empty($item->action)) {
                continue;
            }


            $id = $item->id ? $item->id : html_writer::random_id();
            $content = $item->get_content();
            $title = $item->get_title();
            $ulattr = ['id' => $id . '_group', 'role' => 'group'];
            $liattr = ['class' => [$item->get_css_type(), 'depth_'.$depth], 'role' => 'treeitem'];
            $pattr = ['class' => ['tree_item navtextcolor']];
            $pattr += !empty($item->id) ? ['id' => $item->id] : [];
            $isbranch = $isexpandable && ($item->children->count() > 0 || ($item->has_children() && (isloggedin() || $item->type <= navigation_node::TYPE_CATEGORY)));
            $hasicon = ((!$isbranch || $item->type == navigation_node::TYPE_ACTIVITY || $item->type == navigation_node::TYPE_RESOURCE) && $item->icon instanceof renderable);
            $icon = '';

            if ($hasicon) {
                $liattr['class'][] = 'item_with_icon';
                $pattr['class'][] = 'hasicon';
                $icon = $this->output->render($item->icon);
                // Because an icon is being used we're going to wrap the actual content in a span.
                // This will allow designers to create columns for the content, as we've done in styles.css.
                $content = $icon . html_writer::span($content, 'item-content-wrap');
            }
            if ($item->helpbutton !== null) {
                $content = trim($item->helpbutton).html_writer::tag('span', $content, array('class'=>'clearhelpbutton'));
            }
            if (empty($content)) {
                continue;
            }
            if($depth == 1) {
                $pattr = ['class' => ['tree_item navtextcolor  mydashboardicon']];
            } elseif ($content == 'Courses') {
                $pattr = ['class' => ['tree_item fa fa-briefcase courseiconnavigation']];
            }
            $nodetextid = $htmlidprefix . '_label_' . $depth . '_' . $number;
            $attributes = array('tabindex' => '-1', 'id' => $nodetextid);
            if ($title !== '') {
                $attributes['title'] = $title;
            }
            if ($item->hidden) {
                $attributes['class'] = 'dimmed_text';
            }
            if (is_string($item->action) || empty($item->action) ||
                (($item->type === navigation_node::TYPE_CATEGORY || $item->type === navigation_node::TYPE_MY_CATEGORY) &&
                    empty($options['linkcategories']))) {
                $content = html_writer::tag('span', $content, $attributes);
            } else if ($item->action instanceof action_link) {
                //TODO: to be replaced with something else
                $link = $item->action;
                $link->text = $icon.html_writer::span($link->text, 'item-content-wrap');
                $link->attributes = array_merge($link->attributes, $attributes);
                $content = $this->output->render($link);
            } else if ($item->action instanceof moodle_url) {
                $content = html_writer::link($item->action, $content, $attributes);
            }

            if ($isbranch) {
                $ariaexpanded = $item->has_children() && (!$item->forceopen || $item->collapse);
                $pattr['class'][] = 'branch';
                $liattr['class'][] = 'contains_branch';
                $liattr += ['aria-expanded' => $ariaexpanded ? "false" : "true"];
                if ($item->requiresajaxloading) {
                    $liattr += [
                        'data-requires-ajax' => 'true',
                        'data-loaded' => 'false',
                        'data-node-id' => $item->id,
                        'data-node-key' => $item->key,
                        'data-node-type' => $item->type
                    ];
                } else {
                    $liattr += ['aria-owns' => $id . '_group'];
                }
            }

            if ($item->isactive === true) {
                $liattr['class'][] = 'current_branch';
            }
            if (!empty($item->classes) && count($item->classes)>0) {
                $pattr['class'] = array_merge($pattr['class'], $item->classes);
            }

            $liattr['class'] = join(' ', $liattr['class']);
            $pattr['class'] = join(' ', $pattr['class']);

            $liattr += $depth == 1 ? ['data-collapsible' => 'false'] : [];
            if (isset($liattr['aria-expanded']) && $liattr['aria-expanded'] === 'false') {
                $ulattr += ['aria-hidden' => 'true'];
            }

            // Create the structure.
            $content = html_writer::tag('span', $content, $pattr);
            if ($isexpandable) {

                $content .= $this->navigation_node($item->children, $ulattr, $expansionlimit, $options, $depth + 1);
            }
            if (!empty($item->preceedwithhr) && $item->preceedwithhr===true) {
                $content = html_writer::empty_tag('hr') . $content;
            }

            $liattr['aria-labelledby'] = $nodetextid;
            $content = html_writer::tag('li', $content, $liattr);
            $lis[] = $content;
        }

        if (count($lis) === 0) {
            // There is still a chance, despite having items, that nothing had content and no list items were created.
            return '';
        }

        // We used to separate using new lines, however we don't do that now, instead we'll save a few chars.
        // The source is complex already anyway.
        return html_writer::tag('ul', implode('', $lis), $attrs);
    }
}


class theme_radiant_mod_quiz_renderer extends mod_quiz_renderer {
    
    /**
     * Display the prev/next buttons that go at the bottom of each page of the attempt.
     *
     * @param int $page the page number. Starts at 0 for the first page.
     * @param bool $lastpage is this the last page in the quiz?
     * @param string $navmethod Optional quiz attribute, 'free' (default) or 'sequential'
     * @return string HTML fragment.
     */
     protected function attempt_navigation_buttons($page, $lastpage, $navmethod = 'free') {
  $output = '';

        $output .= html_writer::start_tag('div', array('class' => 'submitbtns'));
        if ($page > 0 && $navmethod == 'free') {
         $output .= html_writer::div(
    html_writer::label(
        '',
        '<i class="fa fa-long-arrow-right transition ml-2 next-tab-clickable"></i>',
        'mod_quiz-next-nav clickable'
    ),
    'clickable-container'
);


            $output .= html_writer::empty_tag('input', array(
                'type' => 'submit',
                'name' => 'previous',
                'value' => get_string('navigateprevious', 'theme_radiant'),
                'class' => 'mod_quiz-prev-nav btn btn-secondary',
                'id' => 'mod_quiz-prev-nav'
            ));

            $this->page->requires->js_call_amd('core_form/submit', 'init', ['mod_quiz-prev-nav']);
        }
        if ($lastpage) {
            $nextlabel = get_string('endtest', 'quiz');
        } else {
            $nextlabel = get_string('quiznavigation', 'theme_radiant');
        }
           $output .= html_writer::empty_tag('input', array(
            'type' => 'submit',
            'name' => 'next',
            'value' => $nextlabel,
            'class' => 'mod_quiz-next-nav btn btn-primary',
            'id' => 'mod_quiz-next-nav'
        ));

    $output .= html_writer::div(
    html_writer::label(
        '',
        '<i class="fa fa-long-arrow-right transition ml-2 next-tab-clickable"></i>',
        'mod_quiz-next-nav clickable'
    ),
    'clickable-container'
);


        /*
        $output .= html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'next',
            'value' => $nextlabel, 'class' => 'mod_quiz-next-nav btn btn-primary', 'id' => 'mod_quiz-next-nav'));
        $output .= \html_writer::div('<i class="fa fa-long-arrow-right transition ml-2"></i>');
        */
        $output .= html_writer::end_tag('div');
        $this->page->requires->js_call_amd('core_form/submit', 'init', ['mod_quiz-next-nav']);

        return $output;
    }
    
    /**
     * Render the tertiary navigation for pages during the attempt.
     *
     * @param string|moodle_url $quizviewurl url of the view.php page for this quiz.
     * @return string HTML to output.
     */
    public function during_attempt_tertiary_nav($quizviewurl): string {
        global $OUTPUT, $DB, $COURSE;
        // Get the course module object
        $cmid = optional_param('cmid', '', PARAM_INT);

        if ($cmid) {
            $cm = get_coursemodule_from_id('', $cmid, 0, false, MUST_EXIST); // Get the course module from the database.
            $module = $DB->get_record($cm->modname, array('id' => $cm->instance), '*', MUST_EXIST); // Get the module (resource) from the database.
            $section = $DB->get_record('course_sections', array('id' => $cm->section), '*', MUST_EXIST); // Get the section from the database.
            $filename = $module->name; // Get the filename of the video resource.
            $sectionname = $section->name; // Get the name of the section the resource is in.
            if(empty($sectionname)) {
                // Load the course module
                $coursemodule = get_coursemodule_from_id('', $cmid, 0, false, MUST_EXIST);
                // Get the course object
                $course = get_course($coursemodule->course);
                // Create the course sections if they don't exist
                course_create_sections_if_missing($course, array($coursemodule->section));
                // Load the course module info
                $modinfo = get_fast_modinfo($course);
                // Get the section of the course module
                $sectionid = $modinfo->get_section_info($coursemodule->section)->id;
                $section = $modinfo->get_section_info($sectionid, IGNORE_MISSING);
                $sectionname = get_section_name($course, $sectionid);
            }
        }

        $quizid = $DB->get_field('course_modules', 'instance', ['id' => $cmid]);
        $total_questions = $DB->count_records('question_attempts', array('questionusageid' => $quizid));

        $sql = "SELECT q.id
        FROM mdl_quiz q
        JOIN mdl_course_modules cm ON cm.instance = q.id
        WHERE cm.id = $cmid
        AND cm.course = $COURSE->id";
        $quizid = $DB->get_field_sql($sql);
        // Get the number of questions in the quiz
        /*
        $results = $DB->count_records('question_attempts', array('questionusageid' => $quizid));
        */
        $results = $DB->count_records('quiz_slots', array('quizid' => $quizid));
        $data = [
            'file_name' => $filename,
            'questions' => range(1, $results),
            'total_questions' => $results,
            'name' => $sectionname,
            'link' => [
                'url' => $quizviewurl,
                'label' => get_string('back'),
            ],
        ];
         if (!strpos($_SERVER['REQUEST_URI'], '/mod/quiz/summary.php') !== false) {
            $data['donotload'] = true;
        }
      
        return $OUTPUT->render_from_template('theme_radiant/mod_quiz_format', $data);
    }

}


class theme_radiant_core_question_renderer extends core_question_renderer {
     /**
     * Generate the display of a question in a particular state, and with certain
     * display options. Normally you do not call this method directly. Intsead
     * you call {@link question_usage_by_activity::render_question()} which will
     * call this method with appropriate arguments.
     *
     * @param question_attempt $qa the question attempt to display.
     * @param qbehaviour_renderer $behaviouroutput the renderer to output the behaviour
     *      specific parts.
     * @param qtype_renderer $qtoutput the renderer to output the question type
     *      specific parts.
     * @param question_display_options $options controls what should and should not be displayed.
     * @param string|null $number The question number to display. 'i' is a special
     *      value that gets displayed as Information. Null means no number is displayed.
     * @return string HTML representation of the question.
     */
    public function question(question_attempt $qa, qbehaviour_renderer $behaviouroutput,
                             qtype_renderer $qtoutput, question_display_options $options, $number) {

        $output = '';
        $output .= html_writer::start_tag('div', array(
            'id' => $qa->get_outer_question_div_unique_id(),
            'class' => implode(' ', array(
                'que',
                $qa->get_question(false)->get_type_name(),
                $qa->get_behaviour_name(),
                $qa->get_state_class($options->correctness && $qa->has_marks()),
            ))
        ));

        $output .= html_writer::tag('div',
            $this->info($qa, $behaviouroutput, $qtoutput, $options, $number),
            array('class' => 'info'));

        $output .= html_writer::start_tag('div', array('class' => 'content'));
        $output .= html_writer::tag('span', 'Question No. '.$number. '', array('class' => 'helptooltip'));
        $output .= html_writer::tag('div',
            $this->add_part_heading($qtoutput->formulation_heading(),
                $this->formulation($qa, $behaviouroutput, $qtoutput, $options)),
            array('class' => 'formulation clearfix'));
        $output .= html_writer::nonempty_tag('div',
            $this->add_part_heading(get_string('feedback', 'question'),
                $this->outcome($qa, $behaviouroutput, $qtoutput, $options)),
            array('class' => 'outcome clearfix'));
        $output .= html_writer::nonempty_tag('div',
            $this->add_part_heading(get_string('comments', 'question'),
                $this->manual_comment($qa, $behaviouroutput, $qtoutput, $options)),
            array('class' => 'comment clearfix'));
        $output .= html_writer::nonempty_tag('div',
            $this->response_history($qa, $behaviouroutput, $qtoutput, $options),
            array('class' => 'history clearfix border p-2'));

        $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');
        return $output;
    }

    /**
     * Generate the display of the question number.
     * @param string|null $number The question number to display. 'i' is a special
     *      value that gets displayed as Information. Null means no number is displayed.
     * @return HTML fragment.
     */
    protected function number($number) {
        global $OUTPUT, $DB, $COURSE, $USER;
        if (trim($number) === '') {
            return '';
        }

        $cmid = optional_param('cmid', '', PARAM_INT);
        if($cmid) {
            $sql = "SELECT q.id
        FROM mdl_quiz q
        JOIN mdl_course_modules cm ON cm.instance = q.id
        WHERE cm.id = $cmid
        AND cm.course = $COURSE->id";
        $quizid = $DB->get_field_sql($sql);
        // Get the number of questions in the quiz/
        /*
        $results = $DB->count_records('question_attempts', array('questionusageid' => $quizid));
        */
         $results = $DB->count_records('quiz_slots', array('quizid' => $quizid));
        $number_two =  $number - 1;
        $percent_complete = intval(($number_two/$results) * 100);
        $data = array(
            'percent_complete' => $percent_complete,
            'number' => trim($number),
            'isInformation' => (trim($number) === 'i'),
            'information' => get_string('information', 'question'),
            'questionx' => get_string('questionx', 'question', $number),
        );
          if (!strpos($_SERVER['REQUEST_URI'], '/mod/quiz/review.php') !== false) {
            $data['donotload'] = true;
        }
        return $OUTPUT->render_from_template('theme_radiant/mod_quiz_question', $data);
        }
    }
}