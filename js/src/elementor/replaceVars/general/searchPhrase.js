/**
 * Returns the replacement for the %%searchphrase%% variable.
 * @returns {string} The searchphrase string.
 */
function getReplacement() {
	return window.wpseoScriptData.analysis.plugins.replaceVars.searchphrase || "";
}

/**
 * Replaces the %%searchphrase%% variable in a text if in scope.
 *
 * @param {string} text The text to replace the variable in.
 * @returns {string} The modified text.
 */
export default function replace( text ) {
	return text.replace(
		new RegExp( "%%searchphrase%%", "g" ),
		getReplacement()
	);
}