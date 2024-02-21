<?php
//require_once (dirname(__FILE__) . '/config.php');
require_once(__DIR__ . '/../config.php');
require_once($CFG->dirroot. '/theme/radiant/lib.php');
global $DB, $CFG,$OUTPUT,$PAGE;

if (!isset($CFG->additionalhtmlhead)) {
    $CFG->additionalhtmlhead = '';
}


$hash = required_param('hash', PARAM_ALPHANUM);
$firstname = optional_param('firstname','', PARAM_ALPHANUM);
$lastname = optional_param('lastname','', PARAM_ALPHANUM);
$conthash = get_content_hash_by_image_name($hash);
$badgedata = $DB->get_record_sql("SELECT b.id,b.name,
                                                 b.type,
                                                 if(b.courseid != '', b.courseid, 1) AS courseid,
                                                 b.description,
                                                 b.timecreated,
                                                 b.issuername,
                                                 b.issuercontact,
                                                 f.contextid
                                          FROM {files} f INNER JOIN {badge} b ON f.itemid = b.id
                                          WHERE f.contenthash LIKE '%".$conthash."%'");

$url = $CFG->wwwroot."/badges/shared_badges.php?hash=".$hash;

$img = $CFG->wwwroot."/badges/public_badge_imgs/".$conthash.".png";
$title = $badgedata->name;
$description = $badgedata->description;
move_badge_img_file($hash);

/*@author Kalpana P
* image url mismatch */
if($badgedata->courseid !=1 ){
    $context = ($badgedata->type == BADGE_TYPE_SITE) ? context_system::instance() : context_course::instance($badgedata->courseid);
    $imageurl = moodle_url::make_pluginfile_url($context->id, 'badges', 'badgeimage', $badgedata->id, '/', 'f1', false);
}else{
    $imageurl = moodle_url::make_pluginfile_url(context_system::instance()->id, 'badges', 'badgeimage', $badgedata->id, '/', 'f1', false);
}
$CFG->additionalhtmlhead .= '<meta property="og:title" content="'.$title.'" />';
$CFG->additionalhtmlhead .= '<meta property="og:type" content="website" />';
$CFG->additionalhtmlhead .= '<meta property="og:url" content="'.$url.'" />';
$CFG->additionalhtmlhead .= '<meta property="og:image" content="'.$img.'" />';
$CFG->additionalhtmlhead .= '<meta property="og:description" content="'.$description.'" />';

$PAGE->set_pagelayout('admin');
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->css('/badges/share.css');
$PAGE->requires->js('/local/participant/js/jquery-3.6.0.js');
$PAGE->requires->js('/badges/share.js');

echo $OUTPUT->header();

require_once($CFG->dirroot. '/badges/lib.php');
require_once($CFG->libdir . '/badgeslib.php');
require_once($CFG->libdir . '/filelib.php');
$items = $dl = array();

$issued_badge = new \core_badges\output\issued_badge( $hash );
$badge = new badge($issued_badge->badgeid);
$userinfo = $issued_badge->recipient;
$myrenderer = $PAGE->get_renderer('badges');

?>

    <style type="text/css">
        .badge-area-title{
            border-bottom: 1px solid #d7dce0;
            color: #1ba2dd;
            font-size: 13px !important;
            padding: 3px 0px;
            margin-top: 15px;
        }
        .badge-area-label,.lableclss{
            font-weight: 700;
            margin-top: 10px;
            display: inline-block;
        }
        .badge-container-left{
            text-align: center;
        }
        button[type="submit"]{
            height: 29px;
            margin-top: 15px;
            border-radius: 2px !important;
        }
        #div_shares{
            position: relative;
            top: 23px;
        }
        .def_list{
            margin-top: 10px;
        }
        .issue_name{
            left: -15px;
            position: relative;
        }
        .img_size{
            width: 100%;
        }
    </style>

    <div class="row content px-0">
    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-2">
        <div class="badge-container-left">
            <img class="img_size" src="<?php echo $imageurl; ?>">


            <?php
            if ($USER->id == $userinfo->id && !empty($CFG->enablebadges)) {
                echo $myrenderer->single_button(
                    new moodle_url('/badges/badge.php', array('hash' => $hash, 'bake' => true)),
                    get_string('download'),
                    'POST');
            }
            ?>
            <div id="div_shares">
                <div id="shareon">Share On:</div>
                <?php
                echo '<script>
                                  $(document).ready(function(){
                                        $("#share_button").jsSocials({
                                            url: "'.$CFG->wwwroot.'/badges/shared_badges.php?hash='.$hash.'&lastname='.$lastName.'&firstname='.$firstName.'",
                                            shares: [
                                                      { share: "facebook", logo: "fa fa-facebook-square" },
                                                      { share: "twitter", logo: "fa fa-twitter-square" },
                                                      { share: "linkedin", logo: "fa fa-linkedin-square" },
                                                    ],
                                            shareIn: "popup",
                                            showLabel: false,
                                            showCount: false,
                                        });
                                  });
                                  </script>';
                ?>
                <div id="share_button" hash="<?php echo $hash; ?>"></div>
            </div>

        </div>
    </div>
    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-10">
        <div class="row">
            <div class="col-sm-6 col-md-6 col-lg-6">
                <div class="badge-area-title"><?php echo get_string('Recipient_Details', 'local_participant')?></div>
                <div>
                    <div class="badge-area-label"><?php echo get_string('badges_name', 'local_participant')?> </div>

                    <div class="badge-area-value"> <?php echo $issued_badge->recipient->firstname." ".$issued_badge->recipient->lastname; ?></div>
                </div>
            </div>
            <div class="col-sm-6 col-md-6 col-lg-6">
                <div class="badge-area-title"><?php echo get_string('badges_issuer', 'local_participant')?></div>
                <div class="row">
                    <div class="col-sm-3 col-md-3 col-lg-3">
                        <div class="badge-area-label issue_name"><?php echo get_string('badges_issuer_name', 'local_participant')?> </div>

                        <div class="badge-area-value issue_name"> <?php echo $badgedata->issuername; ?></div>
                    </div>
                    <div class="col-sm-3 col-md-3 col-lg-3">
                        <div class="badge-area-label"><?php echo get_string('badges_issuer_contact', 'local_participant')?></div>
                        <div class="badge-area-value"><?php echo $badgedata->issuercontact; ?></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- row close -->

        <div class="row">
            <div class="col-sm-12 col-md-12 col-lg-12">
                <div class="badge-area-title"><?php echo get_string('badges_details', 'local_participant')?> </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 col-md-6 col-lg-6">
                <div>
                    <div class="badge-area-label"><?php echo get_string('badges_name', 'local_participant')?> </div>
                    <div class="badge-area-value"> <?php echo $badgedata->name; ?></div>
                </div>
            </div>
            <div class="col-sm-6 col-md-6 col-lg-6">
                <div>
                    <div class="badge-area-label"><?php echo get_string('badges_desc', 'local_participant')?></div>
                    <div class="badge-area-value"><?php echo $badgedata->description; ?></div>
                </div>
            </div>
        </div>
        <div class="row">
        </div>

        <div class="row">
            <div class="col-sm-12 col-md-12 col-lg-12">
                <div class="badge-area-title"><?php echo get_string('badges_expiry', 'local_participant')?></div>
                <div>
                    <div class="badge-area-label"><?php echo get_string('badges_date', 'local_participant')?></div>
                    <div class="badge-area-value"> <?php echo userdate($issued_badge->dateissued); ?></div>
                </div>

            </div>
        </div>
    </div>

<?php

echo $OUTPUT->footer();