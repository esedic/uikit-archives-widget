<?php
/**
 * Plugin Name: UIkit Archives Widget
 * Description: A custom archives widget styled for UIkit accordion navigation.
 * Version: 1.2
 * Author: Elvis Sedić
 */

class UIkit_Archives_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'uikit_archives_widget',
            __('UIkit Archives', 'textdomain'),
            array('description' => __('Archives grouped by year for UIkit accordion', 'textdomain'))
        );
    }
    
    public function widget($args, $instance) {
        global $wpdb;
        
        $title = !empty($instance['title']) ? $instance['title'] : __('Archives', 'textdomain');
        $current_year = (int) date('Y');
        
        echo $args['before_widget'];
        
        if ($title) {
            echo $args['before_title'] . esc_html($title) . $args['after_title'];
        }
        
        // Get all archives
        $archives = $wpdb->get_results("
            SELECT YEAR(post_date) AS year, MONTH(post_date) AS month, COUNT(ID) as posts
            FROM $wpdb->posts
            WHERE post_type = 'post' AND post_status = 'publish'
            GROUP BY year, month
            ORDER BY post_date DESC
        ");
        
        if ($archives) {
            // Group archives by year
            $grouped = [];
            foreach ($archives as $archive) {
                $grouped[$archive->year][$archive->month] = $archive->posts;
            }
            
            echo '<ul class="uk-nav-default" uk-nav>';
            
            foreach ($grouped as $year => $months) {
                $open_class = ($year === $current_year) ? ' uk-open' : '';
                echo '<li class="uk-parent' . $open_class . '">';
                echo '<a href="#">' . esc_html($year) . '</a>';
                echo '<ul class="uk-nav-sub uk-padding-remove-top">';
                
                foreach ($months as $month => $count) {
                    $month_name = date_i18n('F', mktime(0, 0, 0, $month, 1));
                    $url = get_month_link($year, $month);
                    
                    echo '<li>';
                    echo '<a href="' . esc_url($url) . '">';
                    echo esc_html(strtolower($month_name) . " $year ($count)");
                    echo '</a>';
                    echo '</li>';
                }
                
                echo '</ul>';
                echo '</li>';
            }
            
            echo '</ul>';
        }
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Archives', 'textdomain');
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php _e('Title:'); ?>
            </label>
            <input class="widefat" 
                   id="<?php echo esc_attr($this->get_field_id('title')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>" 
                   type="text" 
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = [];
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        return $instance;
    }
}

function register_uikit_archives_widget() {
    register_widget('UIkit_Archives_Widget');
}
add_action('widgets_init', 'register_uikit_archives_widget');