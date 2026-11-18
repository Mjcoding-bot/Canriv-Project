<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'Canriv' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'L+D0*V`%+i(I]sOqy$Arfq)hNLwng@){64;s!LQpO~eQW(Ocx=ZCG^),AwH#D<D^' );
define( 'SECURE_AUTH_KEY',  '-^COY?<1bo`|($`Vw0s2,8DV 5cB2(HH|iL*wFak=Y!]1opO:&^@Ze@]+#CSa^m5' );
define( 'LOGGED_IN_KEY',    'DO7<bH6r1V <u-`+/U+cB+.b{m? %RvmNbMmU^/$^k!Zy*RB4ys(aH-5 X+uHwbM' );
define( 'NONCE_KEY',        'p:GWmB&pG/P/PZr`]sW-p^ZZKq|MIh8k5k|h!]SKTKKqA|!Y3cjIjnMKfwd|M~W/' );
define( 'AUTH_SALT',        'l=4p+RaVVi=.u,!?08aiZ`VY WsY>/>fK2}&;Bu~OxMxi+?JQqGHWA2Qze,KxU#{' );
define( 'SECURE_AUTH_SALT', '4vD3iv*8|ada,VpG=lY-fHRk;xLVXb(F1*Q-wfoQS~@yN}W7jc5J5P,9ciW}Fu!R' );
define( 'LOGGED_IN_SALT',   'PJeMU#kxIJ#(L,aor8MtZ;.{ZB4>p@zXL.LxCs;[:WHl5o/8M__/YZG5?N@.xC#k' );
define( 'NONCE_SALT',       'bUpUu$v(z.>B,hs`6j(*6dh([^FhJ .p0)JrupOvOg9BCkA5[y|K_slewMXL9)&;' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
