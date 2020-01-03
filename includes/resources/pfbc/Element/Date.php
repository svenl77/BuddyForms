<?php

/**
 * Class Element_Date
 */
class Element_Date extends Element_Textbox {
	/**
	 * Element_Date constructor.
	 *
	 * @param $label
	 * @param $name
	 * @param $field_options
	 * @param array|null $properties
	 */
	public function __construct( $label, $name, $field_options, array $properties = null ) {
		$element_class = ' bf_datetimepicker ';
		if ( ! empty( $properties['class'] ) ) {
			$properties['class'] .= sprintf( " %s ", $element_class );
		} else {
			$properties['class'] = sprintf( " %s ", $element_class );
		}

		$show_label = isset( $field_options['is_inline'] ) && isset( $field_options['is_inline'][0] ) && $field_options['is_inline'][0] === 'is_inline';
		if ( $show_label ) {
			$properties['label'] = $label;
		}

		parent::__construct( $label, $name, $properties, $field_options );
	}

	public static function validateDate( $date, $format = 'y/m/d hh:mm tt' ) {
		$d = DateTime::createFromFormat( $format, $date );

		return $d && $d->format( $format ) == $date;
	}

	public function render() {
		wp_enqueue_script( 'buddyforms-jquery-ui-timepicker-addon-js', BUDDYFORMS_ASSETS . 'resources/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.js', array(
			'jquery',
			'jquery-ui-datepicker',
			'jquery-ui-core',

		) );
		wp_enqueue_script( 'buddyforms-moment-js', BUDDYFORMS_ASSETS . 'resources/moment.min.js', array( 'jquery' ), '2.24.0' );
		wp_enqueue_style( 'buddyforms-jquery-ui-themes', BUDDYFORMS_ASSETS . 'resources/jquery-ui-timepicker-addon/jquery-ui.css', 'all', '1.1.0' );
		wp_enqueue_style( 'buddyforms-jquery-ui-timepicker-addon-css', BUDDYFORMS_ASSETS . 'resources/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.css' );

		$expected_format = ! empty( $this->field_options['element_date_format'] ) ? $this->field_options['element_date_format'] : 'y/m/d';

		if ( ! empty( $this->field_options['enable_time'] ) && $this->field_options['enable_time'][0] == 'enable_time' ) {
			$expected_format .= ' ';
			$expected_format .= ! empty( $this->field_options['element_time_format'] ) ? $this->field_options['element_time_format'] : 'hh:mm tt';
		}

		if ( ! empty( $this->field_options['element_date_format'] ) ) {
			$this->setAttribute( 'data-format', $this->field_options['element_date_format'] );
			$this->setAttribute( 'date-validation', true );
		}

		if ( ! empty( $expected_format ) ) {
			$this->validation[] = new Validation_Date ( "Error: The %element% field must match the following date format: " . ! empty( $expected_format ) ? $expected_format : '', $this->field_options );
		}
		parent::render();
	}
}
