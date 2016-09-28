<?php

if (!empty($data)) {
  ?>
  <div class="bh_share_icons_wrap clearfix clear">
    <ul class="bh_share_icons">
      <li>Share this:</li>
    <?php
    foreach ($data as $icon => $link) {
      ?>
      <li class="bh_<?php echo $icon; ?>">
        <a class="<?php echo ($icon != 'mail' ? 'bh_nw': ''); ?>" href="<?php echo $link; ?>" title="share on <?php echo $icon; ?>"><i class="socicon-<?php echo $icon; ?>"></i></a>
      </li>
      <?php
    }
    ?>
    </ul>
  </div>
  <?php
}
?>