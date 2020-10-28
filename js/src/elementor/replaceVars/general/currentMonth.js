/**
 * Returns the replacement for the %%currentmonth%% variable.
 * @returns {string} The current month string.
 */
function getReplacement() {
	return window.wpseoScriptData.analysis.plugins.replaceVars.currentmonth || "";
}

/**
 * Replaces the %%currentmonth%% variable in a text if in scope.
 *
 * @param {string} text The text to replace the variable in.
 * @returns {string} The modified text.
 */
export default function replace( text ) {
	return text.replace(
		new RegExp( "%%currentmonth%%", "g" ),
		getReplacement()
	);
}