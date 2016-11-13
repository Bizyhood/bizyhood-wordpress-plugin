<?php
  header('Content-Type: '.feed_content_type('rss-http').'; charset='.get_option('blog_charset'), true);
  echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>';
?>
<rss version="2.0"
        xmlns:content="http://purl.org/rss/1.0/modules/content/"
        xmlns:wfw="http://wellformedweb.org/CommentAPI/"
        xmlns:dc="http://purl.org/dc/elements/1.1/"
        xmlns:atom="http://www.w3.org/2005/Atom"
        xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
        xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
        <?php do_action('rss2_ns'); ?>>
<channel>
        <title><?php bloginfo_rss('name'); ?> - Feed</title>
        <atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
        <link><?php bloginfo_rss('url') ?></link>
        <description><?php bloginfo_rss('description') ?></description>
        <lastBuildDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_lastpostmodified('GMT'), false); ?></lastBuildDate>
        <language><?php echo get_option('rss_language'); ?></language>
        <sy:updatePeriod><?php echo apply_filters( 'rss_update_period', 'daily' ); ?></sy:updatePeriod>
        <sy:updateFrequency><?php echo apply_filters( 'rss_update_frequency', '1' ); ?></sy:updateFrequency>
        <?php do_action('rss2_head'); ?>
        
        <?php
        
          $data_page_id    = Bizyhood_Utility::getOption(Bizyhood_Core::KEY_PROMOTIONS_PAGE_ID);

          foreach ($data as $entry) {
            $single_link    = get_permalink( $data_page_id ).$entry['business_identifier'].'/'.$entry['identifier'].'/';
          ?>
            <item>
              <title><?php echo htmlspecialchars($entry['name']); ?></title>
              <link><?php echo $single_link; ?></link>
              <pubDate><?php echo $entry['start']; ?></pubDate>
              <dc:creator><?php echo htmlspecialchars($entry['business_name']); ?></dc:creator>
              <guid isPermaLink="false"><?php echo $single_link; ?></guid>
              <description><![CDATA[<?php echo strip_tags($entry['details']); ?>]]></description>
              <content:encoded><![CDATA[<?php echo $entry['details']; ?>]]></content:encoded>
              <?php rss_enclosure(); ?>
              <?php do_action('rss2_item'); ?>
            </item>
          <?php
          }
        ?>
</channel>
</rss>