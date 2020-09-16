/* global jQuery, elementor, $e, elementorFrontend */
import ElementorEditorData from "./analysis/elementorEditorData";
import YoastView from "./elementor/YoastView";
import initElementorEditorIntegration from "./initializers/elementor-editor-integration";
import initElementorEdit from "./initializers/elementor-edit";

window.yoast = window.yoast || {};
window.yoast.initEditorIntegration = initElementorEditorIntegration;
window.yoast.EditorData = ElementorEditorData;

const addYoastRegion = ( regions ) => {
	regions.yoast = {
		region: regions.global.region,
		view: YoastView,
		options: {},
	};

	return regions;
};

jQuery( function() {
	initElementorEdit();

	elementor.once( "preview:loaded", () => {
		let refreshed = false;

		// Connects the tab to the panel.
		$e.components
			.get( "panel/elements" )
			.addTab( "yoast", { title: "Yoast SEO" } );

		// Updates the initial data after the content is available.
		elementorFrontend.hooks.addAction( "frontend/element_ready/global", () => {
			if ( refreshed ) {
				return;
			}

			refreshed = true;
			window.editorData._data = window.editorData.collectData();
			window.YoastSEO.app.refresh();
		} );
	} );
} );

jQuery( window ).on( "elementor:init", () => {
	// Adds the tab to the template.
	const templateElement = document.getElementById( "tmpl-elementor-panel-elements" );
	templateElement.innerHTML = templateElement.innerHTML.replace(
		/(<div class="elementor-component-tab elementor-panel-navigation-tab" data-tab="global">.*<\/div>)/,
		"$1<div class=\"elementor-component-tab elementor-panel-navigation-tab elementor-active\" data-tab=\"yoast\">Yoast SEO</div>",
	);

	elementor.hooks.addFilter(
		"panel/elements/regionViews",
		addYoastRegion,
	);
} );
