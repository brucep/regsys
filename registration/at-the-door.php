<?php get_header(); ?>

<p><?php printf(__('Preregistration for this event end on: %s', 'nsevent'), $event->get_date_prereg_end('c')); ?></p>

<p>Check out at-the-door prices for individual dances on the <a href="/venues/">Venues</a> page.</p>

<?php get_footer(); ?>
