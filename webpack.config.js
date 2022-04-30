const defaultConfig = require("@wordpress/scripts/config/webpack.config");

module.exports = {
	...defaultConfig,
	entry: {
		...defaultConfig.entry,
		tracking: "./src/tracking.js",
	},
};
