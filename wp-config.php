<?php
define( 'WP_CACHE', true );
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'ecommerce' );

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
define( 'AUTH_KEY',         'tQXnr0?eNSMG~$Y]>Kp?Vr%mdV`MSy0_J-QAQ=^L!ae87BcugdA?1f5*6y&L_[/8' );
define( 'SECURE_AUTH_KEY',  'C|]#w_JN%%45TxiZU F.,x<9XM{HC8b`99rF()LiD]]!VU%u>]06,G3gA?(~#z<+' );
define( 'LOGGED_IN_KEY',    '{]aIji|i{YHDrV[8W+7KW/<m9=!8xHG3ED&%y`De}zfhsF]HZc4tSi2T`{JR~/lC' );
define( 'NONCE_KEY',        '01U~mR&~kBSRk#dQ`==,6W`Q2EsMpi^x6H$}[sG,nS]fHU/,2d6V66~yD@Ni0v7s' );
define( 'AUTH_SALT',        '{BT%7:jS4e>l; B4s!oLhD9>q1^.dUFie0_T.B*:3*UkYX+MI{uk1X*{mv>(2T=o' );
define( 'SECURE_AUTH_SALT', '<!Xp(Kdo,t1;U6*Z4,g]5V;4 Tt0$u[.WZlx-_c>?*xL/5gJqCj5!/9>^KI)P- y' );
define( 'LOGGED_IN_SALT',   '~O`nd)KE)Z|oIav_-X9R1U?/-_[Q[F5c-?L` hKRg8Xs(fprEuMp[-VaXar0<s$B' );
define( 'NONCE_SALT',       'K_!+=8<Gn_MjM!0,y5y/l`?lq24P)G$3:^UR3dnUjvmqJcC^KXe9S.iAE|l<006Z' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
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
