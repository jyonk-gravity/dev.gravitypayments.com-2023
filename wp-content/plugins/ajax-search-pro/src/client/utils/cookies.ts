/**
 * A utility module for handling browser cookies.
 * Provides functions to create, read, and erase cookies with flexible expiration options.
 */

type CookieOptions = {
	/**
	 * Expiration time in days.
	 */
	days?: number;
	/**
	 * Expiration time in hours.
	 */
	hours?: number;
	/**
	 * Expiration time in minutes.
	 */
	minutes?: number;
	/**
	 * The path where the cookie is accessible. Defaults to '/'.
	 */
	path?: string;
	/**
	 * The domain where the cookie is accessible. Defaults to current domain.
	 */
	domain?: string;
	/**
	 * Indicates if the cookie should only be transmitted over secure protocols like HTTPS.
	 */
	secure?: boolean;
	/**
	 * The SameSite attribute controls whether a cookie is sent with cross-site requests.
	 * Can be 'Strict', 'Lax', or 'None'.
	 */
	sameSite?: 'Strict' | 'Lax' | 'None';
};

/**
 * Creates a cookie with the specified name, value, and options.
 *
 * @param name - The name of the cookie.
 * @param value - The value of the cookie.
 * @param options - Optional settings for the cookie's expiration and attributes.
 */
export const createCookie = (
	name: string,
	value: string,
	options: CookieOptions = {}
): void => {
	if (!name) {
		throw new Error("Cookie name is required.");
	}

	const { days, hours, minutes, path = '/', domain, secure, sameSite } = options;

	let expires = '';
	if (days || hours || minutes) {
		const date = new Date();
		const totalMilliseconds =
			(days || 0) * 24 * 60 * 60 * 1000 +
			(hours || 0) * 60 * 60 * 1000 +
			(minutes || 0) * 60 * 1000;
		date.setTime(date.getTime() + totalMilliseconds);
		expires = `; expires=${date.toUTCString()}`;
	}

	// Encode name and value to handle special characters
	let cookieString = `${encodeURIComponent(name)}=${encodeURIComponent(value)}${expires}; path=${path}`;

	if (domain) {
		cookieString += `; domain=${domain}`;
	}

	if (secure) {
		cookieString += '; secure';
	}

	if (sameSite) {
		cookieString += `; samesite=${sameSite}`;
	}

	document.cookie = cookieString;
};

/**
 * Reads the value of a cookie by its name.
 *
 * @param name - The name of the cookie to read.
 * @returns The cookie value if found, otherwise `null`.
 */
export const readCookie = (name: string): string | null => {
	if (!name) {
		throw new Error("Cookie name is required.");
	}

	const encodedName = encodeURIComponent(name) + "=";
	const decodedCookies = decodeURIComponent(document.cookie);
	const cookiesArray = decodedCookies.split('; ');

	for (const cookie of cookiesArray) {
		if (cookie.startsWith(encodedName)) {
			return cookie.substring(encodedName.length);
		}
	}

	return null;
};

/**
 * Erases a cookie by setting its expiration date to a past date.
 *
 * @param name - The name of the cookie to erase.
 * @param options - Optional settings for the cookie's path and domain to ensure proper deletion.
 */
export const eraseCookie = (name: string, options: Partial<CookieOptions> = {}): void => {
	if (!name) {
		throw new Error("Cookie name is required.");
	}

	// To erase a cookie, set its expiration date to the past
	createCookie(name, '', { ...options, days: -1 });
};
