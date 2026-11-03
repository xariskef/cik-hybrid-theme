#!/bin/sh
# Minimal content for the e2e smoke suite on a fresh wp-env site.
# Runs inside the wp-env cli container (see lifecycleScripts in .wp-env.json),
# from the WordPress root. Safe to run again: it stops after the first run.
set -eu

if [ "$(wp option get cik_hybrid_e2e_seeded 2>/dev/null || true)" = "1" ]; then
	echo "Smoke content already seeded."
	exit 0
fi

wp theme activate cik-hybrid-theme
wp rewrite structure '/%postname%/' --hard
wp option update blogdescription 'Smoke test site'

image=$(wp media import wp-admin/images/wordpress-logo.png --title='Smoke image' --alt='WordPress logo' --porcelain)

# The front page slider needs two or more posts with a featured image,
# the topic tabs need two or more categories with posts.
news=$(wp term create category News --porcelain)
guides=$(wp term create category Guides --porcelain)
for i in 1 2 3 4; do
	if [ $((i % 2)) -eq 0 ]; then category=$news; else category=$guides; fi
	post=$(wp post create --post_status=publish --post_title="Smoke post $i" \
		--post_excerpt="Excerpt of smoke post $i." --post_content="<p>Body of smoke post $i.</p>" \
		--post_category="$category" --porcelain)
	wp post meta set "$post" _thumbnail_id "$image"
done

about=$(wp post create --post_type=page --post_status=publish --post_title='About' --post_name=about \
	--post_content='<p>About this site.</p>' --porcelain)
wp post create --post_type=page --post_status=publish --post_title='Latest posts block' \
	--post_name=latest-posts-block --post_content='<!-- wp:cik-hybrid/latest-posts {"count":3,"columns":3} /-->'

wp menu create Primary
wp menu item add-post primary "$about"
wp menu item add-custom primary Blog /
wp menu location assign primary primary

if wp plugin is-active woocommerce; then
	product=$(wp post create --post_type=product --post_status=publish --post_title='Smoke product' --porcelain)
	wp post meta set "$product" _regular_price 10
	wp post meta set "$product" _price 10
fi

wp option update cik_hybrid_e2e_seeded 1
