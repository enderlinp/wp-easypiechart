<?php
/*
Plugin Name: WP Easy Pie Chart
Plugin URI: https://www.github.com/enderlinp/wp-easypiechart
Description: WordPress plugin to insert <a href="https://rendro.github.io/easy-pie-chart">rendro's Easy Pie Chart</a> into your articles and pages with a single shortcode.
Version: 1.0
Author: enderlinp
Author URI: https://www.github.com/enderlinp
Licence: MIT
Licence URI: https://www.github.com/enderlinp/wp-easypiechart/LICENSE
Text domain: wp-easypiechart
Domain Path: languages
*/
defined( 'ABSPATH' ) or die( 'Plugin file cannot be accessed directly.' );

if (! class_exists( 'WP_easyPieChart' ) )
{
    class WP_easyPieChart
    {
        /**
         * Unique identifier used by file includes and selector attributes.
         *
         * @var string
         */
        protected string $domain = 'wp-easypiechart';
        
        /**
         * User friendly plugin name.
         *
         * @var string
         */
        protected string $name = 'WP Easy Pie Chart';
        
        /**
         * Current version of the plugin.
         *
         * @var string
         */
        protected string $version = '1.0';
        
        /**
         * List of options to determine plugin behavior.
         *
         * @var array
         */
        protected array $options = [];
        
        /**
         * List of settings displayed on the admin settings page.
         *
         * @var array
         */
        protected array $settings = [];
        
        /**
         * Initiate the plugin by setting the default values.
         *
         * @access public
         */
        public function __construct()
        {
            load_plugin_textdomain( $this->domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
            
            if ( $options = get_option( $this->domain ) ) {
                $this->options = $options;
            }
            
            // Usage: [easypiechart percent="54"]
            add_shortcode( 'easypiechart', array( &$this, 'shortcode' ) );
            
            if ( is_admin() ) {
                add_action( 'admin_init', array( &$this, 'settings' ) );
            }
            
            $this->settings = [
                'barColor' => [
                    'description' => __( 'The color of the bar, or false to disable rendering.', $this->domain ),
                    'placeholder' => '#ef1e25',
                ],
                'trackColor' => [
                    'description' => __( 'The color of the track, or false to disable rendering.', $this->domain ),
                    'placeholder' => '#f2f2f2',
                ],
                'scaleColor' => [
                    'description' => __( 'The color of the scale lines, or false to disable rendering.', $this->domain ),
                    'placeholder' => '#dfe0d0',
                ],
                'scaleLength' => [
                    'description' => __( 'Length of the scale lines (reduces the radius of the chart).', $this->domain ),
                    'validator'   => 'numeric',
                    'placeholder' => 5,
                ],
                'lineCap' => [
                    'description' => __( 'Defines how the ending of the bar line looks like. Possible values are: butt, round and square.', $this->domain ),
                    'placeholder' => 'round',
                ],
                'lineWidth' => [
                    'description' => __( 'Width of the chart line in px.', $this->domain ),
                    'validator'   => 'numeric',
                    'placeholder' => 3,
                ],
                'size' => [
                    'description' => __( 'Size of the pie chart in px. It will always be a square.', $this->domain ),
                    'validator'   => 'numeric',
                    'placeholder' => 110,
                ],
                'rotate' => [
                    'description' => __( 'Rotation of the complete chart in degrees.', $this->domain ),
                    'validator'   => 'numeric',
                    'placeholder' => 0,
                ],
                'animate' => [
                    'description' => __( 'Object with time in milliseconds and boolean for an animation of the bar growing, or false to deactivate animations.', $this->domain ),
                    'placeholder' => '{duration: 1000, enabled: true}',
                ],
                'easing' => [
                    'description' => __( 'String with the name of a jQuery easing function.', $this->domain ),
                    'placeholder' => 'defaultEasing',
                ],
            ];
        }
        
        /**
         * Allow the shortcode to be used.
         *
         * @access public
         * @param  array       $atts
         * @param  string|null $content
         *
         * @return string
         */
        public function shortcode( array $atts, ?string $content = null ): string
        {
            extract( shortcode_atts( [
                'percent'     => false,
                'label'       => false,
                'barcolor'    => false,
                'trackcolor'  => false,
                'scalecolor'  => false,
                'scalelength' => false,
                'linecap'     => false,
                'linewidth'   => false,
                'size'        => false,
                'rotate'      => false,
                'animate'     => false,
                'easing'      => false
            ],
            $atts ) );
            
            // List of shortcode attributes and their jQuery plugin equivalents
            $trans = [
                'percent'     => 'percent',
                'label'       => 'label',
                'barcolor'    => 'barColor',
                'trackcolor'  => 'trackColor',
                'scalecolor'  => 'scaleColor',
                'scalelength' => 'scaleLength',
                'linecap'     => 'lineCap',
                'linewidth'   => 'lineWidth',
                'size'        => 'size',
                'rotate'      => 'rotate',
                'animate'     => 'animate',
                'easing'      => 'easing'
            ];
            
            // Retrieve plugin options
            $options_array = [];
            foreach ( $this->options as $key => $value ) {
                $options_array[$key] = esc_attr( $value );
            }
            
            // Retrieve shortcode attributes
            $atts_array = [];
            foreach ( $atts as $key => $value ) {
                if (! in_array($key, ['percent', 'scalecolor']) && $value) {
                    $atts_array[$trans[$key]] = esc_attr( $value );
                }
            }
            
            // Merge plugin options and shortcode attributes
            $json_array = array_merge( $options_array, $atts_array );
            
            // Remove unnecessary `"` 
            $json = json_encode( $json_array, JSON_FORCE_OBJECT|JSON_NUMERIC_CHECK );
            $json = preg_replace( ['/"([^"]+)"\s*:\s*/', '/"(\{[\w\s:,]+\})"/'], 
                                  ['$1:', '$1'], 
                                  $json );
            
            // Enqueue the required styles and scripts
            $this->_enqueue();
            
            // Output
            ob_start();
            ?>
            <div class="chart">
                <div class="percentage easyPieChart" data-percent="<?php esc_attr_e( $percent + 1 ); ?>"<?php if ( $scalecolor ) : ?> data-scale-color="<?php esc_attr_e( $scalecolor ); ?>"<?php endif; ?>>
                    <span class="percent"><?php esc_attr_e( $percent ); ?>%</span>
                </div>
                <?php if ( $label ) : ?>
                <div class="label"><?php esc_attr_e( $label ); ?></div>
                <?php endif; ?>
            </div>
            <script type="text/javascript">
            jQuery( document ).ready( function () {
                jQuery('.easyPieChart').easyPieChart(
                    // Configuration goes here
                    <?php echo $json . PHP_EOL; ?>
                );
            } );
            </script>
            <?php 
            return ob_get_clean();
        }
        
        /**
         * Add the setting fields to the `Reading` settings page.
         *
         * @access public
         */
        public function settings()
        {
            $section = 'reading';
            
            add_settings_section(
                $this->domain . '_settings_section',
                $this->name,
                function() {
                    printf( __( '<p>Configuration options for the %s plugin.</p>', $this->domain ), esc_html( $this->name ) );
                },
                $section
            );
            
            foreach ( $this->settings as $id => $options ) {
                $options['id'] = $id;
                add_settings_field(
                    sprintf( '%s_%s_settings', $this->domain, $id ),
                    $id,
                    array( &$this, 'settings_field' ),
                    $section,
                    $this->domain . '_settings_section',
                    $options
                );
            }
            
            register_setting(
                $section,
                $this->domain,
                array( &$this, 'settings_validate' )
            );
        }
        
        /**
         * Append a settings field to the fields section.
         *
         * @access public
         * @param  array $options
         */
        public function settings_field( array $options = [] )
        {
            $atts = [
                'name'             => sprintf( '%s[%s]', $this->domain, $options['id'] ),
                'type'             => empty( $options['type'] ) ? 'text' : $options['type'],
                'id'               => $options['id'],
                'aria-describedby' => $options['id'] . '-description',
                'value'            => array_key_exists( 'default', $options ) ? $options['default'] : null,
                'class'            => 'regular-text',
                'placeholder'      => isset( $options['placeholder'] ) ? $options['placeholder'] : null
            ];
            
            if ( isset( $this->options[$options['id']] ) ) {
                $atts['value'] = $this->options[$options['id']];
            }
            if ( isset( $options['placeholder'] ) ) {
			    $atts['placeholder'] = $options['placeholder'];
			}
            
            array_walk( $atts, function( &$item, $key ) {
                $item = sprintf( '%s="%s"', esc_attr( $key ), esc_attr( $item ) );
            } );
            
            ?>
            <input <?php echo implode( ' ', $atts ); ?> />
            <?php if ( array_key_exists( 'description', $options ) ) : ?>
            <p class="description" id="<?php esc_html_e( $options['id'] ); ?>-description">
                <?php esc_html_e( $options['description'] ); ?>
            </p>
            <?php endif; ?>
            <?php
        }
        
        /**
         * Validate the settings.
         *
         * @access public
         * @param  array $input
         *
         * @return array
         */
        public function settings_validate( array $input ): array
        {
            $errors = [];
            foreach ( $input as $key => $value ) {
                if ( $value === '' ) {
                    unset( $input[$key] );
                    continue;
                }
                
                $validator = false;
                if ( isset( $this->settings[$key]['validator'] ) ) {
				    $validator = $this->settings[$key]['validator'];
				}
                
                switch ( $validator ) {
                    case 'numeric':
                        if ( is_numeric( $value ) ) {
						    $input[$key] = intval( $value );
						} else {
						    $errors[] = $key . __( ' must be a numeric value.', $this->domain );
						    unset( $input[$key] );
						}
                    break;
                    
                    default:
                        $input[$key] = strip_tags( $value );
                }
            }
            
            if ( count( $errors ) > 0 ) {
                add_settings_error(
                    $this->domain,
                    $this->domain,
                    implode( '<br />', $errors ),
                    'error'
                );
            }
            
            return $input;
        }
        
        /**
         * Enqueue the required scripts and styles, if they haven't previously been queued.
         */
        protected function _enqueue()
        {
            // Define the URL path to the plugin
            $plugin_path = plugin_dir_url( __FILE__ );
            
            // Enqueue the styles if they aren't already
            if (! wp_style_is( $this->domain, 'enqueued' ) ) {
                wp_enqueue_style( 
                    $this->domain, 
                    $plugin_path . 'css/style.css',
                    array(),
                    'all'
                );
            }
            
            // Enqueue the scripts if they aren't already
            if (! wp_script_is( $this->domain, 'enqueued' ) ) {
                wp_enqueue_script( 'jquery' );
                wp_enqueue_script( 
                    'jquery-' . $this->domain, 
                    $plugin_path . 'js/jquery.easypiechart.min.js',
                    array( 'jquery' ),
                    '2.1.7',
                    false
                );
                if ( is_admin() ) {
                    wp_enqueue_script( 
                        $this->domain, 
                        $plugin_path . 'js/jquery.easypiechart.min.js',
                        array( 'jquery-' . $this->domain ),
                        $this->version
                    );
                }
                
                // Make the options available to JavaScript
                $options = array_merge( [
                    'selector' => '.' . $this->domain
                ], $this->options );
                wp_localize_script( $this->domain, $this->domain, $options );
				wp_enqueue_script( $this->domain );
            }
        }
    }
    
    new WP_easyPieChart();
}
