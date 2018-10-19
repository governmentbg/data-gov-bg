
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

require('./bootstrap-tagsinput');
require('./bootstrap-datepicker.min');
require('./bootstrap-datepicker.bg');

// Dropdowns
require('./select2.full.min');

// DataTables
require('datatables.net-dt');

// Checkboxes
require('icheck');

// Main js
require('./custom');
require('./admin');
require('./user');

// Codemirror
require('codemirror');

// Summernote wysiwyg editor
require('summernote');
