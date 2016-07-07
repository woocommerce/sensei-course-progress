<?php
/**
 * Sensei Course Progress Collapse Widget
 *
 * @author 		WooThemes
 * @category 	Widgets
 * @package 	Sensei/Widgets
 * @version 	1.0.0
 * @extends 	WC_Widget
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Sensei_Course_Progress_Collapse_Widget extends WP_Widget {
	protected $woo_widget_cssclass;
	protected $woo_widget_description;
	protected $woo_widget_idbase;
	protected $woo_widget_title;

	/**
	 * Constructor function.
	 * @since  1.1.0
	 * @return  void
	 */
	public function __construct() {
		/* Widget variable settings. */
		$this->woo_widget_cssclass = 'widget_sensei_course_progress';
		$this->woo_widget_description = __( 'Displays the current learners progress within the current course/module (only displays on single lesson page).', 'sensei-course-progress' );
		$this->woo_widget_idbase = 'sensei_course_progress';
		$this->woo_widget_title = __( 'Sensei - Course Progress', 'sensei-course-progress' );
		/* Widget settings. */
		$widget_ops = array( 'classname' => $this->woo_widget_cssclass, 'description' => $this->woo_widget_description );

		/* Widget control settings. */
		$control_ops = array( 'width' => 250, 'height' => 350, 'id_base' => $this->woo_widget_idbase );

		/* Create the widget. */
        parent::__construct( $this->woo_widget_idbase, $this->woo_widget_title, $widget_ops, $control_ops );
    }

	function widget( $args, $instance ) {

		global $woothemes_sensei, $post, $current_user, $view_lesson, $user_taking_course;

        $allmodules = 'off';
		if ( isset( $instance['allmodules'] ) ) {
			$allmodules = $instance['allmodules'];
		}

        // If not viewing a lesson/quiz, don't display the widget
        if( !( ( is_singular('lesson') || is_singular('quiz') ) ) ) return;

        extract( $args );
        if ( is_singular('quiz') ) {
            $current_lesson_id = absint( get_post_meta( $post->ID, '_quiz_lesson', true ) );
        } else $current_lesson_id = $post->ID;

        // get the course for the current lesson/quiz
        $lesson_course_id = get_post_meta( $current_lesson_id, '_lesson_course', true );

        // Check if the user is taking the course
        $is_user_taking_course = WooThemes_Sensei_Utils::user_started_course( $lesson_course_id, $current_user->ID );

        //Check for preview lesson
        $is_preview = false;
        if ( method_exists( 'WooThemes_Sensei_Utils', 'is_preview_lesson' ) ) {
            $is_preview = WooThemes_Sensei_Utils::is_preview_lesson( $post->ID );
        }

		$course_title = get_the_title( $lesson_course_id );
		$course_url = get_the_permalink( $lesson_course_id );

		$in_module = false;
		$lesson_module = '';
		$lesson_array = array();

		if ( 0 < $current_lesson_id ) {
			// get an array of lessons in the module if there is one
			if( isset( Sensei()->modules ) && has_term( '', Sensei()->modules->taxonomy, $current_lesson_id ) ) {
				// Get all modules
    			$course_modules = Sensei()->modules->get_course_modules( $lesson_course_id );
				$lesson_module = Sensei()->modules->get_lesson_module( $current_lesson_id );
				$in_module = true;
				$current_module_title = htmlspecialchars( $lesson_module->name );

				// Display all modules
				if ( 'on' == $allmodules ) {
					foreach ($course_modules as $module) {
						// get all lessons in the module
						$args = array(
							'post_type' => 'lesson',
							'post_status' => 'publish',
							'posts_per_page' => -1,
							'meta_query' => array(
								array(
									'key' => '_lesson_course',
									'value' => intval( $lesson_course_id ),
									'compare' => '='
								)
							),
							'tax_query' => array(
								array(
									'taxonomy' => Sensei()->modules->taxonomy,
									'field' => 'id',
									'terms' => intval( $module->term_id )
								)
							),
							'meta_key' => '_order_module_' . intval( $module->term_id ),
							'orderby' => 'meta_value_num date',
							'order' => 'ASC'
						);
						$lesson_array = array_merge( $lesson_array, get_posts( $args) );
					}
				} else {
					// Only display current module
			    	// get all lessons in the current module
					$args = array(
						'post_type' => 'lesson',
						'post_status' => 'publish',
						'posts_per_page' => -1,
						'meta_query' => array(
							array(
								'key' => '_lesson_course',
								'value' => intval( $lesson_course_id ),
								'compare' => '='
							)
						),
						'tax_query' => array(
							array(
								'taxonomy' => Sensei()->modules->taxonomy,
								'field' => 'id',
								'terms' => $lesson_module
							)
						),
						'meta_key' => '_order_module_' . intval( $lesson_module->term_id ),
						'orderby' => 'meta_value_num date',
						'order' => 'ASC'
					);

					$lesson_array = get_posts( $args );
				}
			} else {
				// if there's no module, get all lessons in the course
				$lesson_array = Sensei()->course->course_lessons( $lesson_course_id );
			}
		}

		echo $before_widget; ?>

		<header>
			<h2 class="course-title"><a href="<?php echo $course_url; ?>"><?php echo $course_title; ?></a></h2>

			<?php if ( $in_module && 'on' != $allmodules ) { ?>
				<h3 class="module-title"><?php echo $current_module_title ; ?></h3>
			<?php } ?>

		</header>

		<?php
		$nav_id_array = sensei_get_prev_next_lessons( $current_lesson_id );
		$previous_lesson_id = absint( $nav_id_array['prev_lesson'] );
		$next_lesson_id = absint( $nav_id_array['next_lesson'] );

		if ( ( 0 < $previous_lesson_id ) || ( 0 < $next_lesson_id ) ) { ?>

			<ul class="course-progress-navigation">
				<?php if ( 0 < $previous_lesson_id ) { ?><li class="prev"><a href="<?php echo esc_url( get_permalink( $previous_lesson_id ) ); ?>" title="<?php echo get_the_title( $previous_lesson_id ); ?>"><span><?php _e( 'Previous', 'sensei-course-progress' ); ?></span></a></li><?php } ?>
				<?php if ( 0 < $next_lesson_id ) { ?><li class="next"><a href="<?php echo esc_url( get_permalink( $next_lesson_id ) ); ?>" title="<?php echo get_the_title( $next_lesson_id ); ?>"><span><?php _e( 'Next', 'sensei-course-progress' ); ?></span></a></li><?php } ?>
			</ul>

		<?php } ?>

		<ul class="course-progress-lessons expListProg">



			<?php

			$old_module = '';

			foreach( $lesson_array as $lesson ) {
				$lesson_id = $lesson->ID;
				$lesson_title = htmlspecialchars( $lesson->post_title );
				$lesson_url = get_the_permalink( $lesson_id );

				// add 'completed' class to completed lessons
				$classes = "not-completed";
				if( WooThemes_Sensei_Utils::user_completed_lesson( $lesson->ID, $current_user->ID ) ) {
					$classes = "completed";
				}

				// Lesson Quiz Meta
                $lesson_quiz_id = Sensei()->lesson->lesson_quizzes( $lesson_id );

				// add 'current' class on the current lesson/quiz
				if( $lesson_id == $post->ID || $lesson_quiz_id == $post->ID ) {
					$classes .= " current";
				}

				if ( isset( Sensei()->modules ) && 'on' == $allmodules ) { ?>  <?php
					$new_module = Sensei()->modules->get_lesson_module( $lesson_id );
					if ( $old_module != $new_module ) {
						?>
						 <li class="course-progress-module expListProgMain"><h3 class="expList3" style='display:inline-block'><i class='expList3 fa tog-mod fa-chevron-down'></i><?php echo ' '.$new_module->name; ?></h3></li>

						<?php
						$old_module = $new_module;
					}
				}

				?>

                    <li class="course-progress-lesson <?php echo $classes; ?>">
                        <?php if( $lesson->ID == $post->ID || $lesson_quiz_id == $post->ID ) {
                            echo '<span>' . $lesson_title . '</span>';
                        } else {
                            echo '<a href="' . $lesson_url . '">' . $lesson_title . '</a>';
                        } ?>
                    </li>

			<?php  } ?>



		<?php echo $after_widget;
	}

	/**
	 * Method to update the settings from the form() method.
	 * @since  1.0.0
	 * @param  array $new_instance New settings.
	 * @param  array $old_instance Previous settings.
	 * @return array               Updated settings.
	 */
	public function update ( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* The check box is returning a boolean value. */
		$instance['allmodules'] = $new_instance['allmodules'];

		return $instance;
	} // End update()

	/**
	 * The form on the widget control in the widget administration area.
	 * Make use of the get_field_id() and get_field_name() function when creating your form elements. This handles the confusing stuff.
	 * @since  1.1.0
	 * @param  array $instance The settings for this instance.
	 * @return void
	 */
    public function form( $instance ) {

		/* Set up some default widget settings. */
		/* Make sure all keys are added here, even with empty string values. */
		$defaults = array(
						'allmodules' => false
					);

		$instance = wp_parse_args( (array) $instance, $defaults );

		if ( isset( Sensei()->modules ) ) {
		?>
				<p>
					<input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id('allmodules') ); ?>" name="<?php echo esc_attr( $this->get_field_name('allmodules') ); ?>"<?php checked( $instance['allmodules'], 'on' ); ?> />
					<label for="<?php echo esc_attr( $this->get_field_id('allmodules') ); ?>"><?php _e( 'Display all Modules', 'woothemes-sensei' ); ?></label><br />
				</p>
		<?php } else { ?>
				<p>There are no options for this widget.</p>
				<?php }
	} // End form()
}