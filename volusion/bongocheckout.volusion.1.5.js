/*		
	Bongo Checkout for Volusion		
	v1.5
	
	By Elijah Boston (elijah.boston@bongous.com / elijahboston.com)	
	
	
	
	REQUIREMENTS:
	---
	- jQuery 1.4.2 or newer
	- A Partner Key and Checkout URL provided by Bongo International
	
	
	
	CHANGELOG:		
	---		
	1.2 - Fixed bug with SKU's containing multiple dashes.
	1.3 - Switch use of jQuery to no conflict mode to resolve issues with other JS libraries.
	1.4 - Fixed issue with Recalculate button not re-appearing.
	1.5 - Resolved compatability with some older version of Volusion.
		- Improved error handling 

*/

var $b = jQuery.noConflict();

function gup( name, url )
{
	name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
	
	var regexS = "[\\?&]"+name+"=([^&#]*)";
	var regex = new RegExp( regexS );
	var results = regex.exec( url );
	
	if( results == null )
		return "";
	else
		return results[1];
}

// Trim string
function trim(stringToTrim) {
	return stringToTrim.replace(/^\s+|\s+$/g,"");
}

function hex2str(hex_encoded_str) {
	var decoded_str = '';
	var hex_code = '';
	var hex_pos = 0;
	var symbol = '';
	
	var hex_pos = hex_encoded_str.indexOf('%');
	if (hex_pos > 0) {
	
			hex_code = hex_encoded_str.substr(hex_pos+1, 2);

			switch (hex_code) {
				case '2D':
					
					symbol = '-';
					break;
				default:

					symbol = '';
					break;
			}
			
			decoded_str = hex_encoded_str.substring(0, hex_pos) + symbol + hex_encoded_str.substring(hex_pos + 3);
			
			// Recursively convert all hex in the string
			return hex2str(decoded_str);
			
	} else {
		// This string has no more hex!
		decoded_str = hex_encoded_str;
		return decoded_str;
	}
	
	
}

function getItemIDs() {
    var ids = new Array();
	var ids_filtered = new Array();
    urls = $b('form[name="form"] td a[href^="ProductDetails.asp"]');

	
    for (i=0; i < urls.length; i++) {
		// Convert any hex ASCII into a character with hex2str, after using gup to extract URL parameters
        ids[i] = hex2str(gup('ProductCode', urls[i]));
    }

    return ids;
}

function getItemNames() {
	names = new Array();
	elements = $b('form[name="form"] td a[href^="ProductDetails.asp"] b');
	
	for (i=0;i < elements.length; i++) {
		names[i] = elements[i].innerHTML;
	}
	return names;
}

function getItemQuantities() {
	quantities = new Array();
	elements = $b('form[name="form"] td input[name^="Quantity"]');
	
	for (i=0; i < elements.length; i++) {
		quantities[i] = elements[i].value;
	}
	
	return quantities;
}

function getItemPrices() {
	price = '';
	prices = new Array();
	elements = $b('form[name="form"] td div font:contains("$")').not(':odd');
	
	for (i=0; i < elements.length; i++) {
		price = trim(elements[i].innerHTML);
		prices[i] = price.substr(1);
	}
	
	return prices;
}

function countryCode(country) {
	var iso_country_code = '';
	
	// ISO 3166-1 country names and codes from http://opencountrycodes.appspot.com/javascript
	countries = [["AF", "Afghanistan"],["AX", "Aland Islands"],["AL", "Albania"],["DZ", "Algeria"],["AS", "American Samoa"],["AD", "Andorra"],["AO", "Angola"],["AI", "Anguilla"],["AQ", "Antarctica"],["AG", "Antigua and Barbuda"],["AR", "Argentina"],["AM", "Armenia"],["AW", "Aruba"],["AU", "Australia"],["AT", "Austria"],["AZ", "Azerbaijan"],["BS", "Bahamas"],["BH", "Bahrain"],["BD", "Bangladesh"],["BB", "Barbados"],["BY", "Belarus"],["BE", "Belgium"],["BZ", "Belize"],["BJ", "Benin"],["BM", "Bermuda"],["BT", "Bhutan"],["BO", "Bolivia, Plurinational State of"],["BQ", "Bonaire, Saint Eustatius and Saba"],["BA", "Bosnia and Herzegovina"],["BW", "Botswana"],["BV", "Bouvet Island"],["BR", "Brazil"],["IO", "British Indian Ocean Territory"],["BN", "Brunei Darussalam"],["BG", "Bulgaria"],["BF", "Burkina Faso"],["BI", "Burundi"],["KH", "Cambodia"],["CM", "Cameroon"],["CA", "Canada"],["CV", "Cape Verde"],["KY", "Cayman Islands"],["CF", "Central African Republic"],["TD", "Chad"],["CL", "Chile"],["CN", "China"],["CX", "Christmas Island"],["CC", "Cocos (Keeling) Islands"],["CO", "Colombia"],["KM", "Comoros"],["CG", "Congo"],["CD", "Congo, The Democratic Republic of the"],["CK", "Cook Islands"],["CR", "Costa Rica"],["CI", "Cote D'ivoire"],["HR", "Croatia"],["CU", "Cuba"],["CW", "Curacao"],["CY", "Cyprus"],["CZ", "Czech Republic"],["DK", "Denmark"],["DJ", "Djibouti"],["DM", "Dominica"],["DO", "Dominican Republic"],["EC", "Ecuador"],["EG", "Egypt"],["SV", "El Salvador"],["GQ", "Equatorial Guinea"],["ER", "Eritrea"],["EE", "Estonia"],["ET", "Ethiopia"],["FK", "Falkland Islands (Malvinas)"],["FO", "Faroe Islands"],["FJ", "Fiji"],["FI", "Finland"],["FR", "France"],["GF", "French Guiana"],["PF", "French Polynesia"],["TF", "French Southern Territories"],["GA", "Gabon"],["GM", "Gambia"],["GE", "Georgia"],["DE", "Germany"],["GH", "Ghana"],["GI", "Gibraltar"],["GR", "Greece"],["GL", "Greenland"],["GD", "Grenada"],["GP", "Guadeloupe"],["GU", "Guam"],["GT", "Guatemala"],["GG", "Guernsey"],["GN", "Guinea"],["GW", "Guinea-Bissau"],["GY", "Guyana"],["HT", "Haiti"],["HM", "Heard Island and McDonald Islands"],["VA", "Holy See (Vatican City State)"],["HN", "Honduras"],["HK", "Hong Kong"],["HU", "Hungary"],["IS", "Iceland"],["IN", "India"],["ID", "Indonesia"],["IR", "Iran, Islamic Republic of"],["IQ", "Iraq"],["IE", "Ireland"],["IM", "Isle of Man"],["IL", "Israel"],["IT", "Italy"],["JM", "Jamaica"],["JP", "Japan"],["JE", "Jersey"],["JO", "Jordan"],["KZ", "Kazakhstan"],["KE", "Kenya"],["KI", "Kiribati"],["KP", "Korea, Democratic People's Republic of"],["KR", "Korea, Republic of"],["KW", "Kuwait"],["KG", "Kyrgyzstan"],["LA", "Lao People's Democratic Republic"],["LV", "Latvia"],["LB", "Lebanon"],["LS", "Lesotho"],["LR", "Liberia"],["LY", "Libyan Arab Jamahiriya"],["LI", "Liechtenstein"],["LT", "Lithuania"],["LU", "Luxembourg"],["MO", "Macao"],["MK", "Macedonia, The Former Yugoslav Republic of"],["MG", "Madagascar"],["MW", "Malawi"],["MY", "Malaysia"],["MV", "Maldives"],["ML", "Mali"],["MT", "Malta"],["MH", "Marshall Islands"],["MQ", "Martinique"],["MR", "Mauritania"],["MU", "Mauritius"],["YT", "Mayotte"],["MX", "Mexico"],["FM", "Micronesia, Federated States of"],["MD", "Moldova, Republic of"],["MC", "Monaco"],["MN", "Mongolia"],["ME", "Montenegro"],["MS", "Montserrat"],["MA", "Morocco"],["MZ", "Mozambique"],["MM", "Myanmar"],["NA", "Namibia"],["NR", "Nauru"],["NP", "Nepal"],["NL", "Netherlands"],["NC", "New Caledonia"],["NZ", "New Zealand"],["NI", "Nicaragua"],["NE", "Niger"],["NG", "Nigeria"],["NU", "Niue"],["NF", "Norfolk Island"],["MP", "Northern Mariana Islands"],["NO", "Norway"],["OM", "Oman"],["PK", "Pakistan"],["PW", "Palau"],["PS", "Palestinian Territory, Occupied"],["PA", "Panama"],["PG", "Papua New Guinea"],["PY", "Paraguay"],["PE", "Peru"],["PH", "Philippines"],["PN", "Pitcairn"],["PL", "Poland"],["PT", "Portugal"],["PR", "Puerto Rico"],["QA", "Qatar"],["RE", "Reunion"],["RO", "Romania"],["RU", "Russian Federation"],["RW", "Rwanda"],["BL", "Saint Barthelemy"],["SH", "Saint Helena, Ascension and Tristan Da Cunha"],["KN", "Saint Kitts and Nevis"],["LC", "Saint Lucia"],["MF", "Saint Martin (French Part)"],["PM", "Saint Pierre and Miquelon"],["VC", "Saint Vincent and the Grenadines"],["WS", "Samoa"],["SM", "San Marino"],["ST", "Sao Tome and Principe"],["SA", "Saudi Arabia"],["SN", "Senegal"],["RS", "Serbia"],["SC", "Seychelles"],["SL", "Sierra Leone"],["SG", "Singapore"],["SX", "Sint Maarten (Dutch Part)"],["SK", "Slovakia"],["SI", "Slovenia"],["SB", "Solomon Islands"],["SO", "Somalia"],["ZA", "South Africa"],["GS", "South Georgia and the South Sandwich Islands"],["ES", "Spain"],["LK", "Sri Lanka"],["SD", "Sudan"],["SR", "Suriname"],["SJ", "Svalbard and Jan Mayen"],["SZ", "Swaziland"],["SE", "Sweden"],["CH", "Switzerland"],["SY", "Syrian Arab Republic"],["TW", "Taiwan, Province of China"],["TJ", "Tajikistan"],["TZ", "Tanzania, United Republic of"],["TH", "Thailand"],["TL", "Timor-Leste"],["TG", "Togo"],["TK", "Tokelau"],["TO", "Tonga"],["TT", "Trinidad and Tobago"],["TN", "Tunisia"],["TR", "Turkey"],["TM", "Turkmenistan"],["TC", "Turks and Caicos Islands"],["TV", "Tuvalu"],["UG", "Uganda"],["UA", "Ukraine"],["AE", "United Arab Emirates"],["GB", "United Kingdom"],["US", "United States"],["UM", "United States Minor Outlying Islands"],["UY", "Uruguay"],["UZ", "Uzbekistan"],["VU", "Vanuatu"],["VE", "Venezuela, Bolivarian Republic of"],["VN", "Viet Nam"],["VG", "Virgin Islands, British"],["VI", "Virgin Islands, U.S."],["WF", "Wallis and Futuna"],["EH", "Western Sahara"],["YE", "Yemen"],["ZM", "Zambia"],["ZW", "Zimbabwe"]];

	for (i=0; i < countries.length; i++) {
		if (country == countries[i][1]) {
			iso_country_code = countries[i][0];
			break;
		}
	}

	return iso_country_code;
}

function getCustInfo() {
	custInfo = new Array();
	
	custInfo['country'] = $b('form[name="form"] select[name="ShipCountry"]').val();
	
	// For older version of Volusion
	if (custInfo['country'] == undefined) { 
		custInfo['country'] = $b('form[name="form"] select[name="Precalc_Country"]').val();
	}
	
	
	
	custInfo['country_2letteriso'] = countryCode(custInfo['country']);
	custInfo['postalCode'] = $b('form[name="form"] input[name="ShipPostalCode"]').val();
	custInfo['state'] = $b('form[name="form"] input[name="ShipState"]').val();
	
	return custInfo;
}

// This is meant to return whether or not the selected country should be handled
// by Bongo or by the defualt checkout.
// Used in case the merchant wants to still handle shipping to some international locations (i.e. Canada)

function isMerchantSupportedCountry(selected_country) {
    var custInfo = getCustInfo();
	
	var supported_countries = $b('form[name="internationalForm"] input[name="MERCHANT_SUPPORTED_COUNTRIES"]').val().split(',');
	
	// In case merchant has not set their supported countries, default to them only supporting domestic US shipments
	if (supported_countries == undefined) { if (selected_country != 'United States') { return false; } }

	for (var i=0; i < supported_countries.length; i++) {
		//$b('#debug').append('Supported Country == ' + supported_countries[i] +'<br />');
		if (supported_countries[i] == selected_country) { return true; }
	}
    
    return false;
}

function showShippingInfo() {
	
	// Show message for intl customers
	if (document.getElementById('intlShippingInfo')){
		$b('#intlShippingInfo').css("display", "block");
		$b('#intlShippingInfo').css("visibility", "visible");
		
		// Hide 'Recalculate' button
		//$b('form[name="form"] input[name="btnRecalculate"]').hide();
	}
}

function hideShippingInfo() {
	// Hide message for US customers
	if (document.getElementById('intlShippingInfo')){
		$b('#intlShippingInfo').css("display", "none");
		$b('#intlShippingInfo').css("visibility", "hidden");
		
		// Show 'Recalculate' button
		//$b('form[name="form"] input[name="btnRecalculate"]').show();
	}
}

function init_intlShippingInfo() {

    var custInfo = getCustInfo();
	if (isMerchantSupportedCountry(custInfo['country'] ) == false) { showShippingInfo(); } else { hideShippingInfo(); }
}

function intlShippingInfo() {
	
	var message = 'Shipping duties, taxes, and fees will be assessed on the next page.';

	// Try to add a hidden box to display a message to the user
	try
	{	

		$b('form[name="form"] select[name="ShipCountry"]').parent().parent().parent().parent().parent().append('<div id="intlShippingInfo"><p><b>International Customers!</b><br />'+message+'</p></div>');
		
		$b('#intlShippingInfo').css("border", "2px solid rgb(255, 101, 1)");
		$b('#intlShippingInfo').css("padding", "5px");
		$b('#intlShippingInfo').css("margin", "10px 0 10px 0");
		$b('#intlShippingInfo').css("display", "none");
		$b('#intlShippingInfo').css("visbility", "hidden");
		$b('#intlShippingInfo').css("width", "280px");

	}
	catch(err) 
	{
	
	}
	
	// For older version of Volusion
	if (!document.getElementById('intlShippingInfo')) {
		try
		{
		
			$b('form[name="form"] select[name="Precalc_Country"]').parent().parent().parent().append('<tr><td colspan="2"><div id="intlShippingInfo"><p><b>International Customers!</b><br />'+message+'</p></div></td></tr>');
		
			$b('#intlShippingInfo').css("border", "2px solid rgb(255, 101, 1)");
			$b('#intlShippingInfo').css("padding", "5px");
			$b('#intlShippingInfo').css("margin", "10px 0 10px 0");
			$b('#intlShippingInfo').css("display", "none");
			$b('#intlShippingInfo').css("visbility", "hidden");
			$b('#intlShippingInfo').css("width", "280px");
	
		}
		catch(err)
		{
		
		}
	
	}
	
	
	
    init_intlShippingInfo();

	$b('form[name="form"] select[name="ShipCountry"]').change( function() {
		if ( isMerchantSupportedCountry($b(this).val()) == false ) { showShippingInfo(); } else { hideShippingInfo(); }
	});
	
	$b('form[name="form"] select[name="Precalc_Country"]').change( function() {
		if ( isMerchantSupportedCountry($b(this).val()) == false ) { showShippingInfo(); } else { hideShippingInfo(); }
	});
}

function internationalOrder() {	


    var itemIDs = new Array();
    var itemNames = new Array();
    var itemQuantities = new Array();
    var itemPrices = new Array();
    
    itemIDs = getItemIDs();
	itemNames = getItemNames();
	itemQuantities = getItemQuantities();
	itemPrices = getItemPrices();
	custInfo = getCustInfo();
	
	
	
	// Change 'YOUR PARTNER KEY' in the line below to the partner key given to you.
	var partner_key = 'b3104642acdc1f0feef91d3e278df839';
	$b('form[name="internationalForm"]').append('<input type="hidden" name="PARTNER_KEY" value="'+partner_key+'">');
	
	
	// Append customer info (Country / State / Zip)
	$b('form[name="internationalForm"]').append('<select style="display:none;" name="CUST_COUNTRY"><option value="'+custInfo['country_2letteriso']+'">'+custInfo['country']+'</option></select> ');
	$b('form[name="internationalForm"]').append('<input style="display:none;" type="text" name="CUST_STATE" value="'+custInfo['state']+'" /> ');
	$b('form[name="internationalForm"]').append('<input style="display:none;" type="text" name="CUST_ZIP" value="'+custInfo['zip']+'" /> ');
	
	// Append info for each item (ID / Name / Price / Quantity)
	for (i=0; i < itemIDs.length; i++) {
		$b('form[name="internationalForm"]').append('<input type="hidden" name="PRODUCT_ID_'+(i+1)+'" value="'+itemIDs[i]+'" /> ');
		$b('form[name="internationalForm"]').append('<input type="hidden" name="PRODUCT_NAME_'+(i+1)+'" value="'+itemNames[i]+'" /> ');
		$b('form[name="internationalForm"]').append('<input type="hidden" name="PRODUCT_PRICE_'+(i+1)+'" value="'+itemPrices[i]+'" /> ');
		$b('form[name="internationalForm"]').append('<input type="hidden" name="PRODUCT_Q_'+(i+1)+'" value="'+itemQuantities[i]+'" /> ');
		$b('form[name="internationalForm"]').append('<input type="hidden" name="PRODUCT_SHIPPING_'+(i+1)+'" value="1.00"> ');
	}
	
	// Submit order info
	$b('form[name="internationalForm"]').submit();

}

function overrideCheckoutButtons() {
    
    if ($b('form[name="form"] select[name="ShippingSpeedChoice"]').val() == undefined ) {
	
		// Override Proceed to Checkout
        $b('form[name="Proceed_To_Checkout_Form"] input[name="btn_checkout_guest"]').click(function(event) {
            event.preventDefault();
            var custInfo = getCustInfo();
            if (isMerchantSupportedCountry(custInfo['country']) == false ) { internationalOrder(); } else { $b('form[name="form"]').submit(); }
        });
		
		$b('form[name="Proceed_To_Checkout_Form"] input[name="btn_checkout_login"]').click(function(event) {
            event.preventDefault();
            var custInfo = getCustInfo();
            if (isMerchantSupportedCountry(custInfo['country'] ) == false) { internationalOrder(); } else { $b('form[name="form"]').submit(); }
        });
    }
}



intlShippingInfo(); // Inject international shipping notification
overrideCheckoutButtons(); // Override behavior of Checkout buttons
