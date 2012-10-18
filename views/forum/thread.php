<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 * 
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

$last_visit = object_get_visit($_SESSION['SessionSeminar'], "forum");
ForumPosting::$course_hashes = ($thread['user_id'] !== $thread['Seminar_id'] ? $thread['Seminar_id'] : false);
$related_users = $thread['context_type'] === "private" ? $thread->getRelatedUsers() : array();

?>
<? if (@$single_thread): ?>
<input type="hidden" id="base_url" value="plugins.php/blubber/forum/">
<input type="hidden" id="context_id" value="<?= htmlReady($thread->getId()) ?>">
<input type="hidden" id="stream" value="thread">
<input type="hidden" id="last_check" value="<?= time() ?>">
<input type="hidden" id="user_id" value="<?= htmlReady($GLOBALS['user']->id) ?>">
<input type="hidden" id="stream_time" value="<?= time() ?>">
<input type="hidden" id="browser_start_time" value="">
<script>jQuery(function () { jQuery("#browser_start_time").val(Math.floor(new Date().getTime() / 1000)); });</script>
<div id="editing_question" style="display: none;"><?= _("Wollen Sie den Beitrag wirklich bearbeiten?") ?></div>
<p>
    <a href="<?= $thread['context_type'] === "course" ? URLHelper::getLink("plugins.php/blubber/forum/forum", array('cid' => $thread['Seminar_id'])) : URLHelper::getLink("plugins.php/blubber/forum/globalstream") ?>">
        <?= Assets::img('icons/16/blue/arr_1left', array('class' => 'text-top')) ?>
        <?= _('Zur�ck zur �bersicht') ?>
    </a>
</p>

<ul id="forum_threads" class="coursestream singlethread">
<? endif; ?>

<li id="posting_<?= htmlReady($thread->getId()) ?>" mkdate="<?= htmlReady($thread['discussion_time']) ?>" class="thread posting<?= $last_visit < $thread['mkdate'] ? " new" : "" ?>" data-autor="<?= htmlReady($thread['user_id']) ?>">
    <div class="hiddeninfo">
        <input type="hidden" name="context" value="<?= htmlReady($thread['Seminar_id']) ?>">
        <input type="hidden" name="context_type" value="<?= $thread['Seminar_id'] === $thread['user_id'] ? "public" : "course" ?>">
    </div>
    <? if ($thread['context_type'] === "course") : ?>
    <a href="<?= URLHelper::getLink("plugins.php/blubber/forum/forum", array('cid' => $thread['Seminar_id'])) ?>"
       <? $title = get_object_name($thread['Seminar_id'], "sem") ?>
       title="<?= _("Veranstaltung")." ".htmlReady($title['name']) ?>"
       class="contextinfo"
       style="background-image: url('<?= CourseAvatar::getAvatar($thread['Seminar_id'])->getURL(Avatar::NORMAL) ?>');">
    </a>
    <? elseif($thread['context_type'] === "private") : ?>
    <? 
        if (count($related_users) > 20) {
            $title = _("Privat: ").sprintf(_("%s Personen"), count($related_users));
        } else {
            $title = _("Privat: ");
            foreach ($related_users as $key => $user_id) {
                if ($key > 0) {
                    $title .= ", ";
                }
                $title .= get_fullname($user_id);
            }
        }
    ?>
    <div class="contextinfo" title="<?= htmlReady($title) ?>" style="background-image: url('<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] ?>/plugins_packages/data-quest/Blubber/assets/images/private.png');">
    </div>
    <div class="related_users"></div>
    <? else : ?>
    <div class="contextinfo" title="<?= _("�ffentlich") ?>" style="background-image: url('<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] ?>/plugins_packages/data-quest/Blubber/assets/images/public.png');">
    </div>
    <? endif ?>
    <div class="avatar_column">
        <div class="avatar">
            <a href="<?= URLHelper::getLink("about.php", array('username' => get_username($thread['user_id']))) ?>">
                <div style="background-image: url('<?= Avatar::getAvatar($thread['user_id'])->getURL(Avatar::MEDIUM)?>');" class="avatar_image"></div>
            </a>
        </div>
    </div>
    <div class="content_column">
        <div class="timer">
            <a href="<?= URLHelper::getLink('plugins.php/blubber/forum/thread/' . $thread->getId(), array('cid' => $thread['Seminar_id'])) ?>" class="permalink" title="<?= _("Permalink") ?>" style="background-image: url('<?= Assets::image_path("icons/16/grey/group") ?>');">
                <span class="time" data-timestamp="<?= (int) $thread['mkdate'] ?>">
                    <?= (date("j.n.Y", $thread['mkdate']) == date("j.n.Y")) ? sprintf(_("%s Uhr"), date("G:i", $thread['mkdate'])) : date("j.n.Y", $thread['mkdate']) ?>
                </span>
            </a>
            <? if (($thread['Seminar_id'] !== $thread['user_id'] && $GLOBALS['perm']->have_studip_perm("tutor", $thread['Seminar_id']))
                    or ($thread['user_id'] === $GLOBALS['user']->id)) : ?>
            <a href="#" class="edit icon" title="<?= _("Bearbeiten") ?>" onClick="return false;" style="background-image: url('<?= Assets::image_path("icons/16/grey/tools") ?>');"></a>
            <? endif ?>
        </div>
        <div class="name">
            <a href="<?= URLHelper::getLink("about.php", array('username' => get_username($thread['user_id']))) ?>">
                <?= htmlReady(get_fullname($thread['user_id'])) ?>
            </a>
        </div>
        <div class="content">
            <? 
            $content = $thread['description'];
            if ($thread['name'] && strpos($thread['description'], $thread['name']) === false) {
                $content = $thread['name']."\n".$content;
            }
            ?>
            <?= ForumPosting::format($content) ?>
        </div>
    </div>

    <ul class="comments">
        <? $postings = $thread->getChildren() ?>
    <? if (count($postings) > 3) : ?>
        <li class="more">
            <?= sprintf(ngettext('%u weiterer Kommentar', '%u weitere Kommentare', count($postings) - 3), count($postings) - 3)?>
            ...
        </li>
    <? endif; ?>
    <? foreach (array_slice($postings, -3) as $posting) : ?>
        <?= $this->render_partial("forum/comment.php", array('posting' => $posting, 'last_visit' => $last_visit)) ?>
    <? endforeach ?>
    </ul>
    <div class="writer">
        <textarea placeholder="<?= _("Kommentiere dies") ?>"></textarea>
    </div>
</li>

<? if (@$single_thread): ?>
</ul>
<? endif; ?>