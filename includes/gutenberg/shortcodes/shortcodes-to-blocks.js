// License: GPLv2+


var el = wp.element.createElement,
    Fragment = wp.element.Fragment,
	registerBlockType = wp.blocks.registerBlockType,
	ServerSideRender = wp.components.ServerSideRender,
    TextControl = wp.components.TextControl,
    SelectControl = wp.components.SelectControl,
    CheckboxControl = wp.components.CheckboxControl,
    ToggleControl = wp.components.ToggleControl,
	InspectorControls = wp.editor.InspectorControls;
;

//
// Embed a form
//
registerBlockType( 'buddyforms/bf-embed-form', {
	title: 'Embed a BuddyForm',
	icon: 'welcome-widgets-menus',
	category: 'buddyforms',

	edit: function( props ) {

        var forms = [
            { value: 'no', label: 'Select a Form' },
        ];
        for (var key in buddyforms_forms) {
            forms.push({ value: key, label: buddyforms_forms[key] });
        }

		return [

			el( ServerSideRender, {
				block: 'buddyforms/bf-embed-form',
				attributes: props.attributes,
			} ),

			el( InspectorControls, {},
                el( SelectControl, {
                    label: 'Please Select a form',
                    value: props.attributes.bf_form_slug,
                    options: forms,
                    onChange: ( value ) => { props.setAttributes( { bf_form_slug: value } ); },
                } ),
			)
		];
	},

	// We're going to be rendering in PHP, so save() can just return null.
	save: function() {
		return null;
	},
} );

//
// Embed a form
//
registerBlockType( 'buddyforms/bf-navigation', {
    title: 'Links to Forms and Post Lists',
    icon: 'welcome-widgets-menus',
    category: 'buddyforms',

    edit: function( props ) {

        var forms = [
            { value: 'no', label: 'Select a Form' },
        ];
        for (var key in buddyforms_forms) {
            forms.push({ value: key, label: buddyforms_forms[key] });
        }

        return [

            el( ServerSideRender, {
                block: 'buddyforms/bf-embed-form',
                attributes: props.attributes,
            } ),

            el( InspectorControls, {},
                el( SelectControl, {
                    label: 'Please Select a form',
                    value: props.attributes.bf_form_slug,
                    options: forms,
                    onChange: ( value ) => { props.setAttributes( { bf_form_slug: value } ); },
                } ),
            )
        ];
    },

    // We're going to be rendering in PHP, so save() can just return null.
    save: function() {
        return null;
    },
} );



//
// Display Submissions
//
registerBlockType( 'buddyforms/bf-list-submissions', {
    title: 'List Submissions',
    icon: 'welcome-widgets-menus',
    category: 'buddyforms',

    edit: function( props ) {
        var className = props.className;
        // var bf_form_slug = props.attributes.bf_form_slug;
        // var bf_permissions = props.attributes.bf_permissions;
        // var bf_author = props.attributes.bf_author;


        // Generate Forms array

        var yesno = [
            { value: 'logged_in_user_only', label: 'Enabled' },
            { value: 'disabled', label: 'Disabled' },
        ];

        var bf_by_author = [
            { value: 'logged_in_user', label: 'Logged in User Posts' },
            { value: 'all_users', label: 'All User Posts' },
            { value: 'author_ids', label: 'Author ID\'S' },
        ];

        var bf_by_form = [
            { value: 'form', label: 'Form Submissions' },
            { value: 'all', label: 'Form selected Post Type' },
        ];

        var bf_list_posts_style_options = [
            { value: 'list', label: 'List' },
            { value: 'table', label: 'Table' },
        ];

        // Generate Forms array
        var forms = [
            { value: 'no', label: 'Select a Form' },
        ];
        for (var key in buddyforms_forms) {
            // console.log(key +' - '+buddyforms_forms[key]);
            forms.push({ value: key, label: buddyforms_forms[key] });
        }

        // Generate Permissions array
        var permission = [
            { value: 'public', label: 'Public (Unregistered Users)' },
            { value: 'private', label: 'Private (Logged in user only) ' },
        ];
        for (var key in buddyforms_roles) {
            // console.log(key +' - '+buddyforms_roles[key]);
            permission.push({ value: key, label: buddyforms_roles[key] });
        }

        return [

            el( ServerSideRender, {
                block: 'buddyforms/bf-list-submissions',
                attributes: props.attributes,
            } ),

            el( InspectorControls, {},
                el( 'p', {}, '' ),
                el( SelectControl, {
                    label: 'Please Select a form',
                    value: props.attributes.bf_form_slug,
                    options: forms,
                    onChange: ( value ) => { props.setAttributes( { bf_form_slug: value } ); },
                } ),
                el( 'p', {}, '' ),
                el( 'b', {}, 'Restrict Access to this Block' ),
                el( SelectControl, {
                    label: 'Permissions',
                    value: props.attributes.bf_rights,
                    options: permission,
                    onChange: ( value ) => { props.setAttributes( { bf_rights: value } ); },
                } ),
                el( 'p', {}, '' ),
                el( 'b', {}, 'Filter Posts' ),
                el( SelectControl, {
                    label: 'by Author',
                    value: props.attributes.bf_by_author,
                    options: bf_by_author,
                    onChange: ( value ) => { props.setAttributes( { bf_by_author: value } ); },
                } ),
                el( TextControl, {
                    label: 'Author ID\'s',
                    value: props.attributes.bf_author_ids,
                    onChange: ( value ) => { props.setAttributes( { bf_author_ids: value } ); },
                } ),
                el( SelectControl, {
                    label: 'by Form',
                    value: props.attributes.bf_by_form,
                    options: bf_by_form,
                    onChange: ( value ) => { props.setAttributes( { bf_by_form: value } ); },
                } ),
                el( TextControl, {
                    label: 'Posts peer page',
                    value: props.attributes.bf_posts_per_page,
                    onChange: ( value ) => { props.setAttributes( { bf_posts_per_page: value } ); },
                } ),
                el( 'p', {}, '' ),
                el( 'b', {}, 'Template' ),
                el( SelectControl, {
                    label: 'List or Table',
                    value: props.attributes.bf_list_posts_style,
                    options: bf_list_posts_style_options,
                    onChange: ( value ) => { props.setAttributes( { bf_list_posts_style: value } ); },
                } ),

            )
        ];
    },

    // We're going to be rendering in PHP, so save() can just return null.
    save: function() {
        return null;
    },
} );
