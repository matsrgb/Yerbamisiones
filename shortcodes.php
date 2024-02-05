<?php
function custom_api_get_filters($atts) {
    // Set a custom time limit (e.g., 30 seconds)
    set_time_limit(3000);

    $args = array(
        'headers' => array(
            'Authorization' => 'Passcode ' . $atts['passcode'],
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/98.0.4758.102 Safari/537.36',
            'X-Requested-With' => 'learn-apmex.com',
        )
    );

    $response = wp_safe_remote_request($atts['url'], $args);

    if (is_wp_error($response)) {
        // Output debugging information
        echo 'Error: Request failed. ' . $response->get_error_message() . "\n";
        return 'Error: Request failed.';
    } else {
        $body = wp_remote_retrieve_body($response);

        if (empty($body)) {
            // Output debugging information
            echo 'Error: Empty response body.' . "\n";
            return 'Error: Empty response body.';
        }

      
        // echo 'Response Body: ' . $body . "\n";

        $data = json_decode($body, true);

        return $data;
    }
}







function custom_api_generate_form($data) {
    $html = '';
    $html .= '<script>';
    $html .= 'function generateURL() {';
    $html .= 'var currentURL = window.location.href;';
    
    $html .= ' var urlParts = currentURL.split("/");';
    $html .= '    console.log(urlParts, "parts");';
    $html .= '    urlParts.pop();';
    $html .= '    urlParts.pop();';
    $html .= '    console.log(urlParts, "parts 2");';
    $html .= '    var base_url = urlParts.join("/");';
    $html .= '    var params = [];';
    $html .= '    console.log(base_url);';
    
    foreach ($data as $key => $values) {
        $param_name = ($key === 'Varieties') ? 'Variety' : (substr($key, -3) === 'ies' ? substr($key, 0, -3) . 'y' : $key);
    
        if ($param_name !== 'Variety') {
            $param_name = substr($param_name, 0, -1);
        }
    
        $param_name = ucfirst($param_name);
    
        $html .= '    var ' . $param_name . ' = document.getElementById("' . $param_name . '").value;';
        $html .= '    if (' . $param_name . ' !== "") {';
        $html .= '        params.push(' . $param_name . ');';
        $html .= '    }';
    }
    
    $html .= 'var final_url = base_url + "/" + params.map(function(param) { return param.replace(/,/g, "-").replace(/ /g, "-"); }).join("-");';

   $html .= 'final_url = final_url.replace(/\\(None\\)/g, "none");';

    $html .= '    console.log(final_url);'; 
    $html .= '    window.location.href = final_url;';
    $html .= '}';
    $html .= '</script>';

    $html .= '<form id="custom-form" method="" class="filter-form-horizontal">';
    $html .= '<div class="filter-cont">';
    $html .= '<h2>Select a Coin:</h2>';
    $html .= '<div class="filter-row">';
    $first = true;
    foreach ($data as $key => $values) {
        // Convert the parameter name to singular form if it ends with "ies"
        $param_name = ($key === 'Varieties') ? 'Variety' : (substr($key, -3) === 'ies' ? substr($key, 0, -3) . 'y' : $key);
        
        if ($param_name !== 'Variety') {
            // Remove the last letter of the parameter name
            $param_name = substr($param_name, 0, -1);
        }
        
        $param_name = ucfirst($param_name); // Capitalize the first letter

        $html .= '<div class="filter-column">'; // Adding a column container for horizontal alignment

        $html .= '<label for="' . $param_name . '">' . $param_name . ':</label>';

        $html .= '<select name="' . $param_name . '" id="' . $param_name . '" ';
        
        if (!$first) {
            $html .= 'disabled';
        } else {
            $first = false;
        }

        $html .= '>'; // Add onchange event

        $html .= '<option value="">-- Select ' . $param_name . ' --</option>'; // Add a default empty option
        foreach ($values as $value) {
            $selected = (isset($_GET[$param_name]) && $_GET[$param_name] === $value) ? 'selected' : '';
            $html .= '<option value="' . $value . '" ' . $selected . '>' . $value . '</option>';
        }
        $html .= '</select>';

        $html .= '</div>'; // Close column container
    }

    $html .= '<input type="button" value="Select" onclick="generateURL();">';
    $html .= '</div>'; // Close row container
    $html .= '</div>';
    $html .= '</form>';

    return $html;
}




global $custom_api_h2_attributes;
$custom_api_h2_attributes = array();

global $custom_api_details_attributes;
$custom_api_details_attributes = array();

global $custom_api_auction_attributes;
$custom_api_auction_attributes = array();


global $custom_api_gallery;
$custom_api_gallery = array();


global $custom_values;
$custom_values = array();

global $second_response;
global $coins;

global $series_name;



// PCG NUMBER REQUEST
function custom_api_second_request($pcgs_number, $passcode) {
    $second_api_url = 'https://www.apmex.com/cvg/coin/' . $pcgs_number;

    $args = array(
        'headers' => array(
            'Authorization' => 'Passcode ' . $passcode,
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/98.0.4758.102 Safari/537.36',
            'X-Requested-With' => 'learn-apmex.com',
        )
    );

    $response = wp_safe_remote_request($second_api_url, $args);

    if (is_wp_error($response)) {
        // Print debugging information
        echo 'Error: Request failed. ' . $response->get_error_message() . "\n";
        return false;
    } else {
        $body = wp_remote_retrieve_body($response);

        if (empty($body)) {
            // Print debugging information
            echo 'Error: Empty response body.' . "\n";
            return false;
        }

        // Output the response body
        // echo 'Response Body: ' . $body . "\n";

        $second_api_response = json_decode($body, true);

        return $second_api_response;
    }
}
function custom_api_details_shortcode($atts) {
    global $custom_api_details_attributes;
    
    // Create the pairs of attributes and labels
    $attributes = array(
        'mintage' => 'Mintage',
        'mint_location' => 'Mint Location',
        'designer' => 'Designer',
        'years_in_production' => 'Years in Production',
        'metal_composition' => 'Metal Composition',
        'diameter' => 'Diameter',
        'weight' => 'Weight',
        'purity' => 'Purity',
    );

    $column1 = '';
    $column2 = '';
    $count = 0;

    foreach ($attributes as $attribute => $label) {
        $attribute_value = !empty($custom_api_details_attributes[$attribute]) ? $custom_api_details_attributes[$attribute] : '--';
  if ($attribute === 'purity') {
        if ($attribute_value === '0.000000') {
            $attribute_value = '--';
        } else {
            $percentage_value = (floatval($attribute_value) / 10) * 100;
            $attribute_value = number_format($percentage_value, 0) . '%';
        }
    }
        // Format the "mintage" attribute with commas
        if ($attribute === 'mintage' && is_numeric($attribute_value)) {
            $attribute_value = number_format($attribute_value);
        }

        // Remove "grams" and "mm" if the attribute value is "N/A"
        if ($attribute_value === '--') {
            $html = '<p class="custom-api-details-p">' . $label . '<br><strong>' . $attribute_value . '</strong></p>';
        } else {
            // Add "mm" after Diameter and "grams" after Weight
            if ($attribute === 'diameter') {
                $attribute_value .= ' mm';
            } elseif ($attribute === 'weight') {
                $attribute_value .= ' grams';
            }

            $html = '<p class="custom-api-details-p">' . $label . ' <strong>' . $attribute_value . '</strong></p>';
        }

        if ($count < 4) {
            $column1 .= $html;
        } else {
            $column2 .= $html;
        }
        $count++;
    }

    // Combine the columns
    $html = '<div class="coin-details">';
    $html .= '<div class="column">' . $column1 . '</div>';
    $html .= '<div class="column">' . $column2 . '</div>';
    $html .= '</div>';

    return $html;
}

add_shortcode('custom_api_details', 'custom_api_details_shortcode');



// Inside your functions.php or custom plugin file

function custom_api_h2_shortcode() {
    $year = get_post_meta(get_the_ID(), 'year', true);
    $mint = get_post_meta(get_the_ID(), 'mint', true);
    $variety = get_post_meta(get_the_ID(), 'variety', true);
    $designation = get_post_meta(get_the_ID(), 'designation', true);

    // Remove hyphens for empty values
    if (empty($year)) {
        $year = '';
    }
    if (empty($mint)) {
        $mint = '';
    }
    if (empty($variety)) {
        $variety = '';
    }
    if (empty($designation)) {
        $designation = '';
    }

    // Create an array of non-empty values
    $parts = array_filter([$year, $mint, $variety, $designation]);

    // Join the array elements with hyphens
    $output = implode(' - ', $parts);

    echo "<h2>{$output}</h2>";
}
add_shortcode('custom_api_h2', 'custom_api_h2_shortcode');


function series_name_shortcode() {
    global $series_name;
    
    if (!empty($series_name)) {
        return '<h1>Series Name: ' . esc_html($series_name) . '</h1>';
    } else {
        return '<h1>Series Name not available.</h1>';
    }
}

add_shortcode('series_name', 'series_name_shortcode');

function custom_api_auction_shortcode($atts) {
    global $custom_api_auction_attributes;

    $atts = shortcode_atts(array(
        'highest_sale_price' => 'Highest Sale Price',
        'most_recent_sale_price' => 'Most Recent Sale Price',
        'quantity_listed_past_year' => 'Quantity Listed Past Year',
    ), $atts);

    $html = '<div class="auction-insights">';
    
    $emptyClass = 'hide-if-empy2'; // Class for hiding the section
    $hasContent = false; // Flag to track if any section has content

    foreach ($atts as $attribute => $label) {
        if ($attribute === 'quantity_listed_past_year' && !empty($custom_api_auction_attributes[$attribute])) {
            $html .= '<div class="custom-api-auction-p"><div class="column-label">' . $label . '</div><div class="column-value">' . $custom_api_auction_attributes[$attribute] . '</div></div>';
            $hasContent = true; // Mark that this section has content
        } elseif (!empty($custom_api_auction_attributes[$attribute])) {
            $html .= '<div class="custom-api-auction-p"><div class="column-label">' . $label . '</br>(any condition)</div><div class="column-value">$' . $custom_api_auction_attributes[$attribute] . '</div></div>';
            $hasContent = true; // Mark that this section has content
        }
    }

    // Check if no section has content, then hide the entire div
    if (!$hasContent) {
        $html = '<style type="text/css">.' . $emptyClass . ' { display: none; }</style>';
    }

    //$html .= '</div>';

    return $html;
}



add_shortcode('custom_api_auction', 'custom_api_auction_shortcode');


global $availability_names;
$availability_names = array();

function custom_api_availability_shortcode($atts) {
    // Initialize the HTML output
    $html = '';

    // Check if the global variable $second_api_response is available
    global $second_response;

    if (isset($second_response['ApmexAvailability']) && is_array($second_response['ApmexAvailability']) && !empty($second_response['ApmexAvailability'])) {
        // Loop through each availability item
        foreach ($second_response['ApmexAvailability'] as $availability) {
            // Check if ProductUrl is available
            if (!empty($availability['ProductUrl'])) {
                // Wrap the div in a link
                $html .= '<a href="' . esc_url($availability['ProductUrl']) . '" target="_blank" style="text-decoration: none;">';
            }
            // Build an HTML card for each item
            $html .= '<div class="availability-card" style="max-width: 200px; border: 1px solid #ccc; padding: 15px; margin: 10px; background-color:white; display: inline-block;">';
            if (!empty($availability['ImageURL'])) {
                $html .= '<img src="' . esc_url($availability['ImageURL']) . '" alt="Item Image" style="max-width: 70%; height: auto; margin:auto; display:block">';
            }
            // Add item details
            $name = esc_html($availability['Name']);
            $html .= '<h3 style="font-size:16px; color:#002539; font-weight:600">' . $name . '</h3>';
            
            // Add the name to the availability_names array
            global $availability_names;
            $availability_names[] = $name;

            $html .= '<div class="as-low" style="margin-top:25px;">';
            $html .= '<p style="font-size:11px; color:black; font-weight:400; margin-bottom:0;">AS LOW AS</p>';
            $html .= '<p style="font-size:17px; color:black; font-weight:600;">$' . esc_html($availability['Sellprice']) . '</p>';
            $html .= '</div>';
            // Check if ProductUrl is available and close the link
            if (!empty($availability['ProductUrl'])) {
                $html .= '</a>';
            }
            // Close the card div
            $html .= '</div>';
        }
    } else {
        // Handle the case where there are no availability items
        $html .= '<style type="text/css">.hide-if-empy { display: none; }</style>';
    }
//echo '<pre>';
  //  print_r($availability_names); // Use print_r to display the array
   // echo '</pre>';
    // Return the generated HTML
    return $html;
}


add_shortcode('custom_api_availability', 'custom_api_availability_shortcode');



// COIN VALUES LIST 
function custom_coin_values_shortcode($atts) {
    global $custom_values;
    global $custom_api_gallery;
    $grades_and_urls = split_grades_and_urls($custom_api_gallery);
    $order = ["MS-67", "MS-66", "MS-65", "MS-64", "MS-63", "Uncirculated", "AlmostUnc-50", "ExtraFine-40", "VeryFine-20", "Fine-12", "VeryGood-8", "Good-4"];
    $atts = shortcode_atts(array(), $atts);

    $html = ''; // Initialize the HTML variable
    $firstViewButtonAdded = false; // Track the first "View" button

    // Sort the $custom_values array based on the order array
    usort($custom_values, function ($a, $b) use ($order) {
        $aIndex = array_search($a['Description'], $order);
        $bIndex = array_search($b['Description'], $order);
        return $aIndex - $bIndex;
    });

    if (!empty($custom_values)) {
        $html .= '<ul class="coin-values">'; // Start an unordered list

        foreach ($custom_values as $item) {
            $description = isset($item['Description']) ? esc_html($item['Description']) : '';
            $value = isset($item['Value']) ? esc_html($item['Value']) : '';

            if ($value === '0') {
                $value = '--';
            } else {
                 $value = '$' . number_format((float)$value);
            }

            // Check for the existence of images for the grade
            $hasImages = isset($grades_and_urls[$description]) && !empty($grades_and_urls[$description]);
            $activeClass = $hasImages && !$firstViewButtonAdded ? ' value-active' : '';

            $html .= '<li class="coin-value-item ' . $description . $activeClass . '">';
            $html .= '<div class="description-column">' . $description . '</div>';
            $html .= '<div class="value-column">' . $value . '</div>';
            $html .= '<div class="third-column">';

            if ($hasImages) {
                $buttonText = $firstViewButtonAdded ? 'View' : 'Viewing';
                $html .= '<button class="value-view" data-grade="' . $description . '">' . $buttonText . '</button>';
                if (!$firstViewButtonAdded) {
                    $firstViewButtonAdded = true; // Mark that the first "View" button is added
                }
            }

            $html .= '</div>';
            $html .= '</li>';
        }

        $html .= '</ul>';
    } else {
        $html = '<p>No custom values available.</p>';
    }

    return $html;
}



add_shortcode('custom_coin_values', 'custom_coin_values_shortcode');


function split_grades_and_urls($inputArray) {
    $result = [];

    foreach ($inputArray as $item) {
        // Split the string into grade and URL parts
        list($gradePart, $urlPart) = explode(',', $item, 2);
        $grade = explode(':', $gradePart)[1];
        $url = explode(':', $urlPart, 2)[1];

        // Ensure the URL is complete
        $url = trim($url);

        // Add the URL to the array under the appropriate grade
        if (!isset($result[$grade])) {
            $result[$grade] = [];
        }
        $result[$grade][] = $url;
    }

    return $result;
}

function custom_api_gallery_shortcode($atts) {
    global $custom_api_gallery;
    $grades_and_urls = split_grades_and_urls($custom_api_gallery);
    $order = ["MS-67", "MS-66", "MS-65", "MS-64", "MS-63", "Uncirculated", "AlmostUnc-50", "ExtraFine-40", "VeryFine-20", "Fine-12", "VeryGood-8", "Good-4"];
    // DOTS
    $year = get_post_meta(get_the_ID(), 'year', true);
    $mint = get_post_meta(get_the_ID(), 'mint', true);
    $variety = get_post_meta(get_the_ID(), 'variety', true);
    $designation = get_post_meta(get_the_ID(), 'designation', true);

    // Remove hyphens for empty values
    if (empty($year)) {
        $year = '';
    }
    if (empty($mint)) {
        $mint = '';
    }
    if (empty($variety)) {
        $variety = '';
    }
    if (empty($designation)) {
        $designation = '';
    }

    // Create an array of non-empty values
    $parts = array_filter([$year, $mint, $variety, $designation]);

    // Join the array elements with hyphens
    $output = implode(' - ', $parts);

    // Encode the array to JSON for use in JavaScript
    $grades_and_urls_json = json_encode($grades_and_urls);
    echo "<script type='text/javascript'>var gradesAndUrls = $grades_and_urls_json;</script>";

    $atts = shortcode_atts(array(), $atts);

    if (!empty($grades_and_urls)) {
        // Find the first grade that has images
        $images_to_display = [];
        $current_grade = '';
        foreach ($order as $grade) {
            if (!empty($grades_and_urls[$grade])) {
                $images_to_display = $grades_and_urls[$grade];
                $current_grade = $grade;
                break; // Stop the loop once the first set of images is found
            }
        }

        // Build the HTML only if images are available
        if (!empty($images_to_display)) {
            $html = '<div class="custom-api-gallery">';
            $html .= '<div class="slider-container">';
            $html .= '<button class="prev-button"><svg width="19" height="30" viewBox="0 0 19 30" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M18.5249 3.525L7.0749 15L18.5249 26.475L14.9999 30L-9.91821e-05 15L14.9999 0L18.5249 3.525Z" fill="#002539" fill-opacity="0.54"/>
        </svg>
        </button>'; // Move the Previous button here

            foreach ($images_to_display as $image_url) {
                $html .= '<div class="slider-item"><img src="' . esc_url($image_url) . '" alt="Gallery Image"></div>';
            }

          $html .= '<button class="next-button"><svg width="19" height="30" viewBox="0 0 19 30" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0 26.475L11.45 15L0 3.525L3.525 0L18.525 15L3.525 30L0 26.475Z" fill="#002539" fill-opacity="0.54"/>
        </svg>
        </button>'; // Move the Next button here
            $html .= '</div>'; // Close slider-container
            $html .= '<div class="gallery-dots">';
            $html .= $output . ' (' . '<strong>' . $current_grade . '</strong>' . ')';
            $html .= '<div class="dots-container">';
            $html .= '<span class="dot"></span>'; // First dot
            $html .= '<span class="dot active-dot"></span>'; // Middle dot (darker)
            $html .= '<span class="dot"></span>'; // Third dot
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>'; // Close custom-api-gallery
        } else {
            $html = '<img src="https://apmex-knowledge-center-clone.flywheelstaging.com/wp-content/uploads/2023/12/NoImageAvailable.png" alt="no images available"/>';
        }

        // JavaScript for slider functionality...
        ob_start();
        ?>
        <script>
       document.addEventListener('DOMContentLoaded', function() {
            var sliderItems = document.querySelectorAll('.slider-item');
            var currentIndex = 0;

            function showSlide(index) {
                sliderItems.forEach(function(item) {
                    item.style.display = 'none';
                });

                sliderItems[index].style.display = 'block';
            }

            function nextSlide() {
                currentIndex = (currentIndex + 1) % sliderItems.length;
                showSlide(currentIndex);
            }

            function prevSlide() {
                currentIndex = (currentIndex - 1 + sliderItems.length) % sliderItems.length;
                showSlide(currentIndex);
            }

            showSlide(currentIndex);

            var nextButton = document.querySelector('.next-button');
            var prevButton = document.querySelector('.prev-button');

            nextButton.addEventListener('click', nextSlide);
            prevButton.addEventListener('click', prevSlide);
        });
        </script>
        <?php
        $html .= ob_get_clean();
    } else {
        $html = '<img src="https://apmex-knowledge-center-clone.flywheelstaging.com/wp-content/uploads/2023/12/NoImageAvailable.png" alt="no images available"/>';
    }

    return $html;
}

add_shortcode('custom_api_gallery', 'custom_api_gallery_shortcode');








function list_compare_shortcode($atts) {
    // Extract shortcode attributes
    $atts = shortcode_atts(array(
        'dynamic_number' => 3,   // Default dynamic number if not provided
    ), $atts);

    $values = retrieve_combinations_data($atts['dynamic_number']);

    // Initialize HTML output
    $html = '';

    if (!empty($values)) {
       $html .= '<div style="overflow:auto;">';
        $html .= '<table class="compare-table" style="overflow:auto;">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>Year</th>';
        $html .= '<th>Mint</th>';
        $html .= '<th>Variety</th>';
        $html .= '<th>Designation</th>';
        $html .= '<th>VG-8</th>';
        $html .= '<th>F-12</th>';
        $html .= '<th>VF-20</th>';
        $html .= '<th>EF-40</th>';
        $html .= '<th>AU-50</th>';
        $html .= '<th>U-60</th>';
        $html .= '<th>MS-63</th>';
        $html .= '<th>MS-64</th>';
        $html .= '<th>MS-65</th>';
        $html .= '<th>MS-66</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        // Loop through each item in the retrieved data and create a table row for each
        foreach ($values as $item) {
        $seriesName = $item['seriesName'];
            $coin_url = '/coin-values/';

            // Check if seriesName ends with the word "value"
            if (preg_match('/\bvalue$/', $seriesName)) {
                $coin_url .= sanitize_title($seriesName);
            } else {
                // Append "-value" if it doesn't end with "value"
                $coin_url .= sanitize_title($seriesName . '-value');
            }

            $coin_url .= '/' . sanitize_title($item['title']);


            $html .= '<tr>';
            $html .= '<td><a style="color: black" href="' . esc_url($coin_url) . '/">' . esc_html($item['year']) . '</a></td>';
            $html .= '<td><a style="color: black" href="' . esc_url($coin_url) . '/">' . esc_html($item['mint']) . '</a></td>';

            // Check if Variety is empty, display "--" if true
            if (empty($item['variety'])) {
                $html .= '<td>--</td>';
            } else {
                $html .= '<td><a style="color: black" href="' . esc_url($coin_url) . '/">' . esc_html($item['variety']) . '</a></td>';
            }

            // Check if Designation is empty, display "--" if true
            if (empty($item['designation'])) {
                $html .= '<td>--</td>';
            } else {
                $html .= '<td><a  style="color: black" href="' . esc_url($coin_url) . '/">' . esc_html($item['designation']) . '</a></td>';
            }

            $html .= formatPriceCell($item['VeryGood_8']);
            $html .= formatPriceCell($item['Fine_12']);
            $html .= formatPriceCell($item['VeryFine_20']);
            $html .= formatPriceCell($item['ExtraFine_40']);
            $html .= formatPriceCell($item['AlmostUnc_50']);
            $html .= formatPriceCell($item['Uncirculated']);
            $html .= formatPriceCell($item['MS_63']);
            $html .= formatPriceCell($item['MS_64']);
            $html .= formatPriceCell($item['MS_65']);
            $html .= formatPriceCell($item['MS_66']);
            $html .= '</tr>';
        }

        $html .= '</tbody>';
        $html .= '</table>';
		$html .= '</div>';
    } else {
        $html .= 'No data available';
    }

    // Return the generated HTML
    return $html;
}



// Register the shortcode
add_shortcode('list_compare', 'list_compare_shortcode');

function formatPriceCell($price) {
    // Check if the price is 0.00, replace with "--" if true
    return $price == '0.00' ? '<td>--</td>' : '<td>$' . number_format($price, 2, '.', '') . '</td>';
}







function retrieve_combinations_data($category_id) {
    global $wpdb;

    // Set the correct table name
    $table_name = 'wp_combinations';

    $query = $wpdb->prepare("SELECT * FROM $table_name WHERE category_id = %d", $category_id);

    $results = $wpdb->get_results($query, ARRAY_A);

    // Print the results for debugging
   // echo "<pre>";
   // print_r($results);
    //echo "</pre>";
    // Return the results
    return $results;
}

function custom_api_get_combinations($atts) {
    // Get the category_id attribute from the shortcode
    $category_id = get_post_meta(get_the_ID(), 'category_id', true);

    // Retrieve the data based on the provided category_id
    $combinations = retrieve_combinations_data($category_id);

    // Add debug information
    error_log('Category ID: ' . $category_id);
    error_log('Combinations data: ' . json_encode($combinations));

    // Format the data as a JSON string
    $combinations_json = json_encode($combinations);

    // Embed the JSON data in a <script> tag
    $script_tag = '<script>var combinations = ' . $combinations_json . ';
    console.log(combinations)</script>';

    // Return the script tag
    return $script_tag;
}

add_shortcode('custom_api_get_combinations', 'custom_api_get_combinations');










/*
/// FIRST VERSION FOR REQUEST FUNCTION SHORTCODE
function custom_api_shortcode($atts) {
    global $custom_api_h2_attributes;
    global $custom_api_details_attributes;
    global $series_name;
    global $custom_api_auction_attributes;
global $custom_api_gallery;
global $custom_values;
global $second_response;
global $coins;
$atts = shortcode_atts(array(
    'url' => '',             // The API endpoint URL
    'passcode' => '',        // The passcode/token value
    'cookie' => '',          // The cookie value (optional)
    'dynamic_number' => 3,   // Default dynamic number if not provided
), $atts);

    if (empty($atts['url']) || empty($atts['passcode'])) {
        return 'Error: API URL or passcode not provided.';
    }

    // Fetch the filters from the API response
    $data = custom_api_get_filters($atts);

    // Filter out empty parameters
    $selected_filters = array_filter($_GET);

    $dynamic_number = isset($atts['dynamic_number']) ? intval($atts['dynamic_number']) : 3;

    // Build the new API request URL based on the dynamic number and selected filters
    $api_url = 'https://www.apmex.com/cvg/series/' . $dynamic_number . '/coin';

    // Append the selected filters to the URL
    $non_empty_params = array_filter($selected_filters, function ($value) {
        return !empty($value);
    });
    
    $query_params = http_build_query($non_empty_params);
    if (!empty($query_params)) {
        $api_url .= '?' . $query_params;
    }

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // You might need to include the Authorization and Cookie headers here if required
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Passcode ' . $atts['passcode'],
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/98.0.4758.102 Safari/537.36'
    ));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    // Disable following redirects
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

    // Execute cURL request
    $response = curl_exec($ch);
    $check_response = json_decode($response, true);
    // Check for cURL errors
    if (isset($check_response['message']) === "No coin found" || isset($check_response['message'])) {
         // Handle the main request failure here
        // You can either show an error message or create a new request

        // Construct a new request with modified parameters
        $modified_atts = $atts;
        // Modify the attributes as needed, for example:
         $modified_atts['url'] = 'https://www.apmex.com/cvg/series/' . $dynamic_number . '/coins';

        // Initialize a new cURL session for the modified request
        $ch2 = curl_init();
        curl_setopt($ch2, CURLOPT_URL, $modified_atts['url']);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);

        // You might need to include the Authorization and Cookie headers here if required
        curl_setopt($ch2, CURLOPT_HTTPHEADER, array(
            'Authorization: Passcode ' . $modified_atts['passcode'],
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/98.0.4758.102 Safari/537.36'
        ));
        curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, true);

        // Disable following redirects
        curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, false);
    
        // Execute the modified cURL request
        $response2 = curl_exec($ch2);

        // Check for cURL errors in the modified request
        if ($response2 === false) {
            $result_html = 'Error: Both main and modified requests failed.';
        } else {
            // Process the response from the modified request
            $api_response2 = json_decode($response2, true);
            if (!$api_response2) {
                $result_html = 'Error: Failed to decode modified API response.';
            } else {
                //echo '<pre>';
               // print_r($api_response2);
               // echo '</pre>';
                $coins = $api_response2;
            }
        }

        // Close the modified cURL session
        curl_close($ch2);
    } else {
        // Process the API response and return the result
        $api_response = json_decode($response, true);
        
        if (!$api_response) {
            $result_html = 'Error: Failed to decode API response.';
        } else {
            // Construct attributes for custom_api_h2 shortcode
            $h2_atts = array(
                'year' => $api_response['Year'],
                'mint' => $api_response['MintMark'],
                'proof' => '', // Add logic to get proof value
                'coin_name' => $api_response['CoinType'],
                'variety' => $api_response['Variety'],
                'designation' => $api_response['Designation'],
            );
            $custom_api_h2_attributes = $h2_atts;
            // Generate H2 content using custom_api_h2 shortcode
            $h2_content = custom_api_h2_shortcode($h2_atts);
          
$series_name = $api_response["CoinGuidSeriesName"];
//print_r($series_name);
// Inside your custom_api_shortcode function
$second_api_response = custom_api_second_request($api_response['PCGSNo'], $atts['passcode'], $atts['cookie']);
$second_response = $second_api_response;
if ($second_api_response !== false) {
    // Set auction insights attributes
    $auction_atts = array(
        'highest_sale_price' => $second_api_response['AuctionInsights']['HighestSalePrice'],
        'most_recent_sale_price' => $second_api_response['AuctionInsights']['MostRecentSalePrice'],
        'quantity_listed_past_year' => $second_api_response['AuctionInsights']['QuantityListedPastYear'],
    );
    $custom_api_auction_attributes = $auction_atts;


    $custom_api_gallery = $second_api_response['Gallery'];
    $custom_values = $second_api_response['CoinValues'];
    // Print the second request response
   // echo "<pre>";
   // print_r($second_api_response);
  // echo "</pre>";
    // Process the second response data as needed
}

$mintage_atts = array(
    'mintage' => $second_api_response['CoinDetails']['Mintage'], // Replace with the actual key from the response

  // 'mint_location' => $api_response['MintLocation'], // Replace with the actual key from the response
   'mint_location' => $second_api_response['CoinDetails']['Mint'], // Replace with the actual key from the response

    'designer' => $second_api_response['CoinDetails']['Designer'], // Replace with the actual key from the response
    'years_in_production' => $second_api_response['CoinDetails']['YearsInProd'], // Replace with the actual key from the response
    'metal_composition' => $second_api_response['CoinDetails']['MetalComposition'], // Replace with the actual key from the response

   // 'diameter' => $api_response['Diameter'], // Replace with the actual key from the response
 'diameter' => $second_api_response['CoinDetails']['Diameter'], // Replace with the actual key from the response

    //'weight' => $api_response['Weight'], // Replace with the actual key from the response
 'weight' => $second_api_response['CoinDetails']['GramWeight'], // Replace with the actual key from the response

   // 'purity' => $api_response['Purity'], // Replace with the actual key from the response
     'purity' => $second_api_response['CoinDetails']['Purity'], // Replace with the actual key from the response

   
);
$custom_api_details_attributes = $mintage_atts;

            // Generate Mintage content using custom_api_mintage shortcode
            $mintage_content = custom_api_details_shortcode($mintage_atts);

            // Combine the form and result HTML
            $result_html = $h2_content . $mintage_content;
            // Combine the form and H2 content
           // $result_html = $h2_content;
         // echo "<pre>";
        //  print_r($api_response);
   //  echo "</pre>";
        }
    }




    // Close cURL session
    curl_close($ch);

    // Display the filtering form
    $form_html = custom_api_generate_form($data);

    // Combine the form and result HTML
   // $full_html = $form_html . $result_html;
    $full_html = $form_html;

    return $full_html;
}

add_shortcode('custom_api_request', 'custom_api_shortcode');

*/










/// FIRST VERSION FOR REQUEST FUNCTION SHORTCODE
function custom_api_shortcode($atts) {
    global $custom_api_h2_attributes;
    global $custom_api_details_attributes;
    global $series_name;
    global $custom_api_auction_attributes;
global $custom_api_gallery;
global $custom_values;
global $second_response;
global $coins;
$atts = shortcode_atts(array(
    'url' => '',             // The API endpoint URL
    'passcode' => '',        // The passcode/token value
    'cookie' => '',          // The cookie value (optional)
    'dynamic_number' => 3,   // Default dynamic number if not provided
), $atts);

    if (empty($atts['url']) || empty($atts['passcode'])) {
        return 'Error: API URL or passcode not provided.';
    }

    // Fetch the filters from the API response
    $data = custom_api_get_filters($atts);

    // Filter out empty parameters
    $selected_filters = array_filter($_GET);

    $dynamic_number = isset($atts['dynamic_number']) ? intval($atts['dynamic_number']) : 3;

    // Build the new API request URL based on the dynamic number and selected filters
   

    // Append the selected filters to the URL
    $non_empty_params = array_filter($selected_filters, function ($value) {
        return !empty($value);
    });
    
    $query_params = http_build_query($non_empty_params);
    if (!empty($query_params)) {
        $api_url .= '?' . $query_params;
    }



            // Construct attributes for custom_api_h2 shortcode
           /*$h2_atts = array(
                'year' => $api_response['Year'],
                'mint' => $api_response['MintMark'],
                'proof' => '', // Add logic to get proof value
                'coin_name' => $api_response['CoinType'],
                'variety' => $api_response['Variety'],
                'designation' => $api_response['Designation'],
            );
            $custom_api_h2_attributes = $h2_atts;
            // Generate H2 content using custom_api_h2 shortcode
            $h2_content = custom_api_h2_shortcode($h2_atts);
          */
//$series_name = $api_response["CoinGuidSeriesName"];
//print_r($series_name);
// Inside your custom_api_shortcode function
$PCGSNo = get_post_meta(get_the_ID(), 'PCGSNo', true);
$second_api_response = custom_api_second_request($PCGSNo, $atts['passcode'], $atts['cookie']);
$second_response = $second_api_response;

if ($second_api_response !== false) {
    // Set auction insights attributes
    $auction_atts = array(
        'highest_sale_price' => $second_api_response['AuctionInsights']['HighestSalePrice'],
        'most_recent_sale_price' => $second_api_response['AuctionInsights']['MostRecentSalePrice'],
        'quantity_listed_past_year' => $second_api_response['AuctionInsights']['QuantityListedPastYear'],
    );
    $custom_api_auction_attributes = $auction_atts;


    $custom_api_gallery = $second_api_response['Gallery'];
    $custom_values = $second_api_response['CoinValues'];
    // Print the second request response
   // echo "<pre>";
   // print_r($second_api_response);
  // echo "</pre>";
    // Process the second response data as needed
}

$mintage_atts = array(
    'mintage' => $second_api_response['CoinDetails']['Mintage'], // Replace with the actual key from the response

  // 'mint_location' => $api_response['MintLocation'], // Replace with the actual key from the response
   'mint_location' => $second_api_response['CoinDetails']['Mint'], // Replace with the actual key from the response

    'designer' => $second_api_response['CoinDetails']['Designer'], // Replace with the actual key from the response
    'years_in_production' => $second_api_response['CoinDetails']['YearsInProd'], // Replace with the actual key from the response
    'metal_composition' => $second_api_response['CoinDetails']['MetalComposition'], // Replace with the actual key from the response

   // 'diameter' => $api_response['Diameter'], // Replace with the actual key from the response
 'diameter' => $second_api_response['CoinDetails']['Diameter'], // Replace with the actual key from the response

    //'weight' => $api_response['Weight'], // Replace with the actual key from the response
 'weight' => $second_api_response['CoinDetails']['GramWeight'], // Replace with the actual key from the response

   // 'purity' => $api_response['Purity'], // Replace with the actual key from the response
     'purity' => $second_api_response['CoinDetails']['Purity'], // Replace with the actual key from the response

   
);
$custom_api_details_attributes = $mintage_atts;

            // Generate Mintage content using custom_api_mintage shortcode
            $mintage_content = custom_api_details_shortcode($mintage_atts);

            // Combine the form and result HTML
            //$result_html = $h2_content . $mintage_content;
            // Combine the form and H2 content
           // $result_html = $h2_content;
         // echo "<pre>";
        //  print_r($api_response);
   //  echo "</pre>";
        
    


    // Display the filtering form
    $form_html = custom_api_generate_form($data);

    // Combine the form and result HTML
   // $full_html = $form_html . $result_html;
    $full_html = $form_html;

    return $full_html;
}

add_shortcode('custom_api_request', 'custom_api_shortcode');



// Custom shortcode to display description based on category ID
function category_description_shortcode($atts) {
    // Get the category ID from the page attribute
    $category_id = get_post_meta(get_the_ID(), 'category_id', true);

    // Define your JSON data as a PHP associative array
   
$json_data = [
    "descriptions" => [
        ["category_id" => 1, "description" => "<p>Chief Mint Engraver Christian Gobrecht and Braided Hair</p>

        <p>Robert Patterson served as Director of the US Mint from 1835 to 1851. In 1840, Patterson decided to include the Half Cent coin in the Proof Sets that the Mint was striking for collectors who wanted one and, especially, for the dignitaries to whom the Mint presented special coin sets. So Patterson instructed the new Chief Engraver, Christian Gobrecht, to create new dies for that purpose.</p>
        
        <p>Gobrecht redesigned the 1839 Large Cent and used that style for the 1840 Half Cents. He radically changed John Reich’s matronly Miss Liberty and gave her a younger, slimmer, more youthful style. Her hair was braided into a bun on the back of her head. She is now wearing a crown or tiara with the word “LIBERTY” emblazoned on it. With the current date below Miss Liberty, he placed 13 stars to surround her. The reverse remained unchanged except for very minor modifications.</p>
        
        <p>Between 1840 and 1849, only proof coins were struck as the available supply of prior dated coins was sufficient to meet the demand. By 1848, there were some 80,000 half cents remaining in the Treasury’s vaults from 1834 and 1835 dates. But a larger shortage of silver coinage around 1849, also affected copper coinage and the Mint kept striking coins for circulation dated 1850 to 1857 when the denomination was discontinued altogether. In the 1850 to 1860 period, coin collecting became a popular hobby in the United States. Collectors of these copper half cents clamored for the Proof only dates of 1840 to 1849 so some of the Mint’s staff found the dies used to strike these proof coins and began striking “a few more coins.” These restrikes can be identified easily from the original proof strikings.  The originals have large berries in the wreath, and the restrikes have small berries. But the mintages never exceeded more than 1,500 coins so they are extremely rare.  Most dates seem to suffer from the black spotting that detracts from the eye appeal of copper coins. Brown or red-brown uncirculated coins are more commonly found.</p>
        
        <p>Copper is a chemically active metal and, depending on the environment, these coins can suffer from carbon spots and corrosion marks. These imperfections must be taken into account when assigning a grade to them. On the obverse study carefully Liberty’s hair is just to the right of her ear and the hair curls on the lower part of her neck as they are the first spots to show the wear of any kind. On the reverse, check for traces of wear on the laurel wreath and on the bow as they are the highest points.</p>"],
        ["category_id" => 2, "description" => "
        <p>Learn About John Reich’s 1809-1836 Classic Half Cents</p>
        
        <p>John Reich was a German medallist and engraver born in 1768. As the 18th Century was drawing to a close, Europe was experiencing political unrest and turmoil. The French Revolution was well underway and unrest with monarchies across the continent was rampant. Given that the newly-formed United States was peaceful, Reich sought passage to America. Arriving in 1800, Reich desperately wanted to work for the US Mint, engraving coins.</p>
        
        <p>Reich’s work came to the attention of President Thomas Jefferson and in 1801 Jefferson recommended that Reich be appointed as Assistant Engraver at the US Mint. But the Chief Engraver, Robert Scot, vehemently opposed Reich’s appointment. There was some professional jealousy at play and although Reich now had a position at the Mint he did not engrave any coins.</p>
        
        <p>By 1807, Scot was now 62 years old and with failing eyesight he no longer opposed Reich’s appointment to the Assistant Engraver’s position. The new Director of the US Mint was Robert Patterson. He believed, as did Jefferson, that Reich’s talents were being wasted and he gave him the mission of redesigning many of the coins then in circulation. Reich redesigned the half dollar, the gold half eagle, the cent, the gold quarter eagle, the dime and finally, the half-cent.</p>
        
        <p>(John Reich’s newly-designed Half Cent)</p>
        
        <p>Reich created a new face for Miss Liberty. She seemed older, less showy, her curls held back on her head by a band prominently inscribed with the word “LIBERTY”. 13 stars encircled her, representing the original colonies and the date was inscribed below. The reverse of the coin remained much unchanged with the denomination in the cent, a wreath around it. The “UNITED STATES OF AMERICA” surmounted the wreath. This design was used long after Reich departed the US Mint. He resigned in 1817, a decade after finally getting his appointment. Reich left because he never had the opportunity to succeed Scot who refused to leave his position. In fact, he never received praise or even a raise from Chief Engraver Scot.</p>
        
        <p>By 1809, the half-cent was losing its value in commerce as inflation increased prices for most goods. The demand for half cents was decreasing. But a bigger problem was growing. The copper planchets used to strike the coins were made in England and the manufacturer had run out of these planchets by 1811. Hostilities occurred between the United States and Great Britain once again and by 1812 there was an embargo on all British goods coming to America.  But once the War of 1812 had ended the production of Half Cents remained stopped for another 14 years.</p>
        
        <p>(John Reich’s Classic Head Half Cent)</p>
        
        <p>By 1825, there was a critical shortage of coinage in the United States. The new Chief Engraver of the Mint, William Kneass slightly modified Reich’s designs and the Mint set about striking coins including the Half Cent.</p>
        
        <p>Anticipating that only large supplies of coins would overtake the shortages, the Mint struck thousands of Half Cents. But either the demand was overblown or the bias against the smallest denomination had grown. By 1830, hundreds of thousands of Half Cents sat idly in the US Mint’s vaults. They were languishing for a lack of demand.</p>
        
        <p>No Half Cents were struck during 1830. Coinage ceased until 1831 when only a couple of thousand coins were struck. Production resumed but was yet again stopped in 1836. Only a small handful of 1836-dated Half Cents was struck, mostly for collectors. Into the 1850s, employees of the Mint who had access to the old dies made illegal strikes on the 1831 and 1836 dates.  Authorized proof coins dated 1836 were struck for the collectors ordering them. But most of the Proofs are later Mint restrikes.</p>
        
        <p>The Classic Head Half Cent series has numerous varieties. 1809 has several varieties, none of them particularly scarce.  1810 escaped the variety challenge. 1811 has 2 minor varieties – a wide date, a close date – and a major rarity – 1811 with a reverse of 1802. It is unofficial; a restrike that is extremely pricey. 1825 and 1826 years were uneventful. 1828 saw two major varieties that are easy to spot – one has 12 stars on the obverse and the other has 13 stars. 1829 is a variety-less year. All 1831 dated Half Cents are proof of Original and restrike issues and all are extremely expensive. 1832 through 1835 are common coins. But 1836, like 1831 is a Proof only date with both Originals and Restrikes and a hefty price tag.</p>"],
        ["category_id" => 3, "description" => "<p>During the years 1798 and 1799 1.8 million Large Cents were minted bearing either of those dates. But no Half Cents were minted during those two years. In 1800, a newly-designed Half Cent entered circulation. The design of Miss Liberty wearing drapery and facing right is attributed to Gilbert Stuart, the American artist, whose most famous work is undoubtedly the unfinished portrait of George Washington.</p>

        <p>Robert Scot, Chief Engraver of the United States Mint, engraved the dies for the first Draped Bust Half Cents. These coins were dated 1800. The portrait of Miss Liberty is said to have been modeled after a Philadelphia society matron, Mrs. William Bingham.</p>
        
        <p>The new 1800-dated Half cent had this new Draped Bust image of Miss Liberty, facing right, with the word “LIBERTY” above her head and the date “1800” below her bust. The reverse design did not change from the previous Liberty Cap design, as it had the words “HALF CENT” in the center of the coin, in 2 lines, a wreath surrounding the denomination, the words “UNITED STATES OF AMERICA” surmounting the wreath and the denomination of “1/200” below the center of the wreath. These coins were struck through 1808.</p>
        
        <p>As there were varieties of the prior design, so were their varieties of this Draped Bust design. There were no varieties of the 1800-dated coins. As there was little demand for this specific denomination, there were no coins struck bearing the 1801 date. In 1802, the first US coin with an overdate was created as there were two varieties of 1802-dated Half Cents and both were over-dates. The two coins were both 1802/0 (1802 over 1800) using the 1800 style reverse and one using a new “2nd reverse.” The 1802/ coin with the Reverse of 1800 is an extremely rare variety. 20,266 coins were struck after the 1800 obverse die was over-dated to 1802 and combined with the two different reverse dies. A very small number shows the 1797 reverse with its single leaves at top and this variety is the most expensive of all Draped Bust Half Cents.</p>
        
        <p>The 1803 date has both a normal date and one where the 3 in the date is widely spaced away from the rest of the date. There are no other varieties. But the 1804 date boasts at least 5 die varieties with the most famous of them all – the 1804 “Spiked Chin” variety – where it appears that a pointed spike is protruding out of Miss Liberty’s chin. This was caused by a foreign object sticking to the die causing what looked like a spike to be coming out of her chin. There are also Plain and Crosslet “4” varieties both with “Stems” and “Stemless” varieties to the wreath.</p>
        
        <p>The 1805-dated coin has 3 varieties – Small, Medium and Large 5 as well as Stems and Stemless reverses for them as well; the 1806-dated coin has 3 varieties – 2 Small 6’s (with and without stems) and 1 Large 6 (with Stems only). There are no varieties of the 1807, but the 1808 has 2 varieties – a Normal Date and 1808/7 overdate.</p>
        
        <p>By the end of 1808, a total of 3,416,950 Draped Bust half cents had been struck. All coins were struck at the Philadelphia Mint and no proof coins or presentation strikes were struck.</p>
        
        <p>All dates of the Draped Bust series are very rare if you can locate one that has a nice red color. Oftentimes any red coins located are spotty at best. The 1800 and 1806 are the only dates that are likely to be found in red. The most attractive coins found today are an even glossy brown color.</p>"],
        ["category_id" => 4, "description" => "
        <p>In April of 1792, the newly formed United States Congress passed the 1792 Coinage Act. This bill provided for the establishment of a United States Mint facility to be built in the then-capital of the United States – Philadelphia. The Act additionally established the United States Dollar as our standard unit of currency, it deemed it to be legal tender and it laid out the regulations for our currency – such as some of the various legends and mottoes that needed to be placed on our coinage.</p>
        
        <p>President George Washington appointed David Rittenhouse to be the first Director of the United States Mint. One of the provisions of the Act was that the President should have his likeness on the obverse of our coinage, just as many world monarchs had their portraits on the coinage of the countries that they ruled. But Washington would have none of that. Since the very first coinage of the Roman Empire, portraits of the reigning emperor always adorned their coins. But Washington did not want to follow England, the country that we had just fought to gain our independence, in having our leaders on our coins. So, it was decided that allegorical representations of Liberty would be the dominant images on our American coinage.</p>
        
        <p>In May of 1792 Washington signed into law an Act to Provide for Copper Coinage which mandated that a half cent coin and one cent coins would be struck. The Director was authorized to purchase up to one hundred fifty tons of copper to be the base material for the coins. Henry Voigt was the Chief Coiner of the US Mint and he looked for inspiration everywhere in designing the country’s first coins.</p>
        
        <p>Augustin Dupre, the French Medallist and Coin Engraver, inspired Voigt by creating a medal called the Libertas Americana medal. Designed by Benjamin Franklin when he was Ambassador to France, and executed by Dupre, the portrait displayed of Miss Liberty facing left, hair blowing in the breeze.</p>
        
        <p>Voigt’s design was well-received, but Washington thought it a bit too simplistic in its design. Nevertheless, production began late in 1793 but only 35,334 coins were struck. The reverse had the words “HALF CENT” inside of a wreath with berries. Below the wreath was the denomination (1/200) and above it was “UNITED STATES OF AMERICA.”</p>
        
        <p>Robert Scot was commissioned as Chief Engraver in November of 1793 and one of his first tasks was to redesign the Half Cent for 1794.  He turned Miss Liberty to face right and enlarged the Cap that was on the pole behind the head of Miss Liberty.  The Cap and pole represented “freemen, who were not enslaved.”</p>
        
        <p>The Half Cents dated 1795 through 1797 were re-cut by Assistant engraver John Gardner who lowered the relief (thickness) of the Miss Liberty design and slightly reduced the size of Miss Liberty herself.</p>
        
        <p>Coins from this early period (1793 to 1797) often vary in quality as the copper blanks on which they were struck often varied in quality themselves.</p>
        
        <p>There are varieties of all these dates that are much rarer and even more valuable. No proof strikings are known. All coins were struck at the Philadelphia Mint.</p>"],
        ["category_id" => 5, "description" => "
        <p>Chief Mint Engraver Christian Gobrecht and Braided Hair</p>
        
        <p>Robert Patterson served as Director of the US Mint from 1835 to 1851. In 1840, Patterson decided to include the Half Cent coin in the Proof Sets that the Mint was striking for collectors who wanted one and, especially, for the dignitaries to whom the Mint presented special coin sets. So Patterson instructed the new Chief Engraver, Christian Gobrecht, to create new dies for that purpose.</p>
        
        <p>Gobrecht redesigned the 1839 Large Cent and used that style for the 1840 Half Cents. He radically changed John Reich’s matronly Miss Liberty and gave her a younger, slimmer, more youthful style. Her hair was braided into a bun on the back of her head. She is now wearing a crown or tiara with the word “LIBERTY” emblazoned on it. With the current date below Miss Liberty, he placed 13 stars to surround her. The reverse remained unchanged except for very minor modifications.</p>
        
        <p>Between 1840 and 1849, only proof coins were struck as the available supply of prior dated coins was sufficient to meet the demand. By 1848, there were some 80,000 half cents remaining in the Treasury’s vaults from 1834 and 1835 dates. But a larger shortage of silver coinage around 1849, also affected copper coinage and the Mint kept striking coins for circulation dated 1850 to 1857 when the denomination was discontinued altogether. In the 1850 to 1860 period, coin collecting became a popular hobby in the United States. Collectors of these copper half cents clamored for the Proof only dates of 1840 to 1849 so some of the Mint’s staff found the dies used to strike these proof coins and began striking “a few more coins.” These restrikes can be identified easily from the original proof strikings. The originals have large berries in the wreath, and the restrikes have small berries. But the mintages never exceeded more than 1,500 coins so they are extremely rare. Most dates seem to suffer from the black spotting that detracts from the eye appeal of copper coins. Brown or red-brown uncirculated coins are more commonly found.</p>
        
        <p>Copper is a chemically active metal and, depending on the environment, these coins can suffer from carbon spots and corrosion marks. These imperfections must be taken into account when assigning a grade to them. On the obverse study carefully Liberty’s hair is just to the right of her ear and the hair curls on the lower part of her neck as they are the first spots to show the wear of any kind. On the reverse, check for traces of wear on the laurel wreath and on the bow as they are the highest points.</p>"],

        ["category_id" => 6, "description" => "
        <p>Learn About 1808 – 1814 Classic Head Large Cents</p>
        
        <p>John Reich was a German-born engraver who immigrated to the US in 1800. His engraving skills came to the attention of President Jefferson who recommended him for a job at the US Mint. He was hired in 1801 but did not ascend to an engraver’s role until 1807.</p>
        
        <p>The US Mint Director, Robert Patterson, assigned Reich the task of redesigning the current coinage. This was much to the dismay of the Chief Engraver Robert Scot, whose designs were now going to be changed by this new, upstart engraver. This caused a great deal of tension between the Chief Engraver of the US Mint and the Assistant Engraver. But Reich followed Patterson’s orders and re-engraved Scot’s designs.</p>
        
        <p>Miss Liberty went from facing right to now facing left. Her long, flowing locks of hair tied with a ribbon, were now pulled back and tucked behind a wide headband upon which was inscribed the word “LIBERTY.” That word would no longer appear above Miss Liberty’s head. Miss Liberty now appeared older and more mature, perhaps in direct correlation to the fact that the country was now older and we were in the 19th Century, not the 18th.</p>
        
        <p>Reich’s design encircled Miss Liberty on the obverse of the coin with 13 stars and the date remained below her bust. On the reverse, the value “ONE CENT” remained centered within the wreath. The words “UNITED STATES OF AMERICA” also remained encircling the wreath. The numerical denomination “1/100” was removed from below the wreath and not used at all.</p>
        
        <p>Reich’s changes were what Patterson wanted to see but they did not please Robert Scot at all. Patterson was the driving force in hiring Reich and Patterson made the point of writing to Jefferson to discuss Scot’s advancing age (he was only 62) and his health. Scot took his frustrations out on Reich and in the 10 years that Reich worked for the US Mint he received neither praise nor a raise from Scot. Reich worked hard to please Patterson redesigning every coin from the Half Cent to the gold $5.00 Half Eagle. But he could not please Scot, which led to Reich’s departure from the US Mint in 1817. He then moved to Albany, NY, where he engraved tokens, medals and other objects.  Reich died in 1833 at age 65.</p>
        
        <p>Just over 1 million 1808-dated Large Cents were produced bearing Reich’s new design. But the Mint soon ran out of blank planchets and 222,867 cents were struck in 1809. An adequate supply of copper planchets in 1810 allowed the Mint to again strike over 1 million coins (1,458,500) and there were two varieties of 1810 dated coins – a normal date and an overdate of 1810/09.</p>
        
        <p>The War of 1812 slowed the shipment of blank planchets to America and the shortage was so great that no 1815-dated copper coins were struck by the US Mint. Boulton and Watt, the British manufacturer of these planchets had great difficulty keeping a steady supply coming to America and also had a difficult time with the inferior quality of the planchets being produced.</p>"],
        ["category_id" => 7, "description" => "
        <p>Matron Head Large Cents – A Large Cent with a Long History</p>
        
        <p>Talk about going from bad to worse! Ever since 1793 when the Mint designed the first US coins for circulation, the designs were ridiculed, derided, scorned and mocked. No one was truly happy with them. They did not have the elegance of their European cousins. They did not have the stability of worldwide acceptance either.</p>
        
        <p>So in an effort to appease the Chief Engraver, the Mint Director asked Robert Scot to try his hand at this once again. Scot had been by-passed for his assistant, John Reich, in creating the prior series of Large and Half Cents (the Classic Head series). In the eyes of the public, Reich’s designs were wrong. The classical women of ancient times never wore a headband such as Miss Liberty wore.</p>
        
        <p>Scot decided to take Miss Liberty in yet another direction. His depiction of her visage shocked those who were hoping for artistic beauty. Miss Liberty, again, was made to look older, heavier and less friendly than ever before. She no longer had a headband but now wore a “Coronet” in her hair, sort of a crown.  Some early coin collectors called this type of Large Cent the Coronet Style. But most collectors thought that too regal. We didn’t want royalty on our coinage as England had. Most collectors instead focused on how Miss Liberty had aged. We now had the “Matron Head” Large Cents.</p>
        
        <p>Miss Liberty now wore a coronet with the word “LIBERTY” in relief across the diadem.  Her face looked considerably older than her predecessor did. This matronly face was going to grace the Large Cents for the next 21 years. Miss Liberty was surrounded by 13 stars and the date was directly beneath her.</p>
        
        <p>The reverse remained largely unchanged with “ONE CENT” in 2 lines in the center of the wreath on the reverse with “UNITED STATES OF AMERICA” around the wreath.</p>
        
        <p>The Mint struck 2,820,982 1816-dated coins. In 1817, Scot created 2 distinct varieties – 15 stars and 13 stars. It is thought that Scot had spaced the stars too close together and rather than scrapping the die he just created, he instead added two additional stars. When Patterson saw the coins as they were already in circulation, he ordered a new die to be created displaying the appropriate 13 stars as mandated by the Coinage Act. Of the 3,948,400 coins dated 1817, it is not known how many of each variety were struck but the numbers must be reasonably close until you get to the higher circulated grades before there is any price difference. 1818 was an uneventful year for these coins with 3,167,000 struck without variety.</p>
        
        <p>1819 had 3 distinct varieties among the 2,671,000 coins struck – an overdate and a Large and Small date all valued similarly. 1820 was the same way with 3 varieties just as 1819. There were 4,407,550 coins struck. 1821 was a scarcer date with no varieties but only 389,000 coins minted. 1822 was uneventful as well but the mintage was considerably higher at 2,072,339.</p>
        
        <p>1823 is the rare date of this series. The exact mintage figures are unknown and may be included with the 1824 dated coins, which only total 1,262,000 coins. 1823 has a 3/2 overdate, a normal date and an “unofficial restrike – struck from a broken obverse die.” All varieties of 1823 are rare and expensive.</p>
        
        <p>1824 had two varieties among the 1,262,000 coins struck while 1825 had no varieties among the 1,461,000 coins struck. 1826 has 2 varieties in the 1,517,425 coins minted. 1827 had no varieties with 2,357,732 coins minted. 1828 has 2,260,624 coins minted and 2 distinct varieties. 1829, 1830, 1831 and 1832 each had the same two varieties – Large Letters and Medium Letters. The mintages ranged from a low of 1,414,500 coins to a high of 3,359,260 coins. 1833 had no varieties among its 2,739,000 coins struck.</p>
        
        <p>1834 had a minimum of 4 distinct varieties among its 1,855,100 coins struck. 1835 had 3 varieties among the 3,878,400 coins bearing that date.</p>
        
        <p>As 1835 approached, a new engraver was asked to “slightly modify” Scot’s “Matron Large Cent” design and that he did.</p>"],
        [
            "category_id" => 8,
            "description" => "<p>Eighteen Styles of Draped Bust Large Cents From 1796-1807</p>
        
            <p>Large Cents were extremely important for commerce at the close of the 18th century but the coins produced by the United States Mint up to this time were not of the highest quality nor were the designs much appreciated. While they were acceptable, they lacked the sophistication of European designs.</p>
        
            <p>The quality of these one-cent coins was another matter entirely. The US Mint had problems obtaining sufficient quantities of quality blanks. As a new customer of the English firms producing these blanks, the US Mint didn’t always receive the best quality blanks. Additionally, the rolling mills that the Mint used to ensure all blanks were the same thickness were, at best, inconsistent. Critique in the press of the day centered on the inexperience of the staff, inadequate funding of the operations and general mismanagement.</p>
        
            <p>Into this maelstrom stepped Robert Scot, who had recently been named as Chief Engraver of the United States Mint, a position in which he served from 1793 until his death in 1823. Scot was born in Scotland and emigrated to the United States in 1775. He moved to Virginia where he engraved plates for Virginia colonial currency. In 1780 he was named the Chief Engraver of the Commonwealth of Virginia. He soon moved to Philadelphia and engraved many projects there. He came to the attention of President Washington who admired his talents. Upon the untimely death of Joseph Wright of yellow fever in Philadelphia in 1793, Scot was named Chief Engraver of the United States Mint.</p>
        
            <p>One of Scot’s early challenges for the US Mint was to create a more refined style of representing Miss Liberty on American coinage. Scot created what has been called the “Draped Bust” design. Modeled after a sketch by Gilbert Stuart, Miss Liberty faces right. A ribbon holds her hair back and a draped gown is at her shoulders. The word “LIBERTY” is above her head and the date is below her.  The reverse depicts an olive wreath with the words “ONE CENT” separated inside the wreath. “UNITED STATES OF AMERICA” surmounts the wreath and a numerical denomination of “1/100” is below. The design was simple yet more sophisticated than previous designs.</p>
        
            <p>This new Draped Bust design replaced the former Liberty Cap design. It was well-received by both the public and by US Mint officials. The 1796-dated Draped Bust Large Cents entered circulation around July of 1796. The Mint struck 363,375 coins, all varieties of Large Cents dated 1796. The obverse was paired with as many as 5 different reverses, one of which is extremely rare and another is just a scarce variety. The rare reverse has no stems coming off the wreath and only 3 examples are known. There is a variety where Liberty is incorrectly spelled as LIHERTY, which is a rather scarce error variety. Spelling and overdate errors are fairly common in early US coinage as their quality control was generally non-existent.</p>
        
            <p>(The new Draped Bust Large Cent – 1796 to 1807)</p>
        
            <p>In 1797, the same obverse die was used and there are 4 distinct coins – one with a Plain Edge, one with a Gripped Edge and 2 with Stems and Stemless Reverses. Approximately 897,510 coins dated 1797 were minted.</p>
        
            <p>For 1798-dated coins, there are 4 varieties, none of which are particularly scarce. There were 1,841,745 coins struck bearing the 1798 date. 1799 dated Draped Bust Large Cents have only two known varieties but both of them are very scarce. There were estimated to have been only 42,540 coins struck but that number is subject to debate by numismatic scholars.</p>
        
            <p>There are three varieties of 1800 Dated Large Cents, none of which is particularly scarce. 2,822,175 coins were struck that were dated 1800. By 1801 mintages stabilized and started to grow. There are 4 varieties of coins and 1,362,837 coins were struck.</p>
        
            <p>In 1802, 3 different varieties were struck and none of them are scarce but a large number of coins were struck – 3,435,100. In 1803, there were six varieties and one of them is extremely scarce. The Large Date with Small Fraction is 100 times rarer than the most common coin in Fine grade. 1804 is a very interesting year in that the Mint only struck 96,500 coins and all of them are rare in any grade. There is also an unofficial restrike, made circa 1860 for collectors. It is only known in uncirculated conditions.</p>
        
            <p>(1804 Unofficial Large Cent)</p>
        
            <p>Heading towards the end of the Draped Bust Large Cents, 1805 saw 941,116 coins struck and 1806 saw 348,000 coins struck and there are no varieties of either date. The final year was 1807 with 829,221 coins struck and 5 varieties of coins were struck with one of them being scarce and 1 of them rare!</p>"
        ],
[
    "category_id" => 9,
    "description" => "<p>Learn About the Flowing Hair Large Cent – 1793 Chain</p>

    <p>The Coinage Act of April 2, 1792, established that there would be a United States Mint with a Director, an Assayer, a Chief Coiner, an Engraver and a Treasurer. The Act also provided that the dollar would be the denomination and it would be equal to the Silver Value of the Spanish Milled Dollar. The Act of May 8, 1792, provided for a Copper Coinage. This Act stated that the Director shall purchase up to 150 tons of copper which shall be made into Cents and Half Cents. That is where this story begins.</p>

    <p>With the mandate from Congress to immediately begin creating a US copper coinage, Chief Engraver Henry Voigt went to work. The obverse of the coin displayed an allegorical representation of Miss Liberty, facing to the right.  Her hair was blowing straight back, as if in a stiff breeze. The Word “LIBERTY” appeared above her head and the date “1793” was below her. Her mouth was slightly opened but her eyes were very wide open.</p>

    <p>(The Obverse of the 1793 Chain Cent.)</p>

    <p>The reverse, which was mired in controversy, depicted a chain of 15 links. These links represented the 15 states, in existence at the time of issue for this coin.  Centered inside the oval chain were the words “ONE CENT” and the denomination, which was expressed as “1/100”. Then surmounting the chain were the words “UNITED STATES OF AMERICA.” These original dies were cut by hand rather than using master hubs as is the practice today.</p>

    <p>As the dies were cut by hand, the lettering was a bit too large for the size of the coin and Voigt modified the word “AMERICA” to “AMERI.” When viewed, the “AMERI.” Was too large for the coin and no one had imagined that the name of the country would be abbreviated. The simple, and correct answer, was to make all of the lettering smaller so the “UNITED STATES OF AMERICA” would fit easily and look proportionate to the size of the coin.</p>

    <p>(1793 Chain Cents – Left: the AMERI. Variety – Right: the AMERICA Variety)</p>

    <p>The edge of all Chain Cents is decorated with leaves, vines and bars. There is no edge lettering.</p>

    <p>The US Mint struck 36,103 coins and released them into circulation. The public reaction was mostly negative. Voigt’s Miss Liberty was mocked as being “creature-like” and “in a fright.” But the reaction to the chain, which was meant to symbolize the unity and strength of the then-15 United States, was highly negative. Several newspapers thought it represented and promoted slavery, and was rebuked as such. As the Mint had run out of blank planchets, more were ordered but the Mint Officials were listening to the public comments and Voigt was ordered to redesign the reverse of the coin. That is how the 1793 Wreath Cent was born.</p>

    <p>There are 3 varieties of the 1793 Chain Cent:</p>

    <p>1. “AMERI.” On the reverse</p>
    <p>2. “AMERICA” on the reverse</p>
    <p>3. Periods after LIBERTY and after 1793 on the obverse</p>

    <p>(1793 Chain Cent with Periods after LIBERTY. and after 1793.)</p>

    <p>By the late 1850’s US coin collectors realized how rare the Chain Cent was and how few of the original mintage was still in existence. The coin was highly desirable even then. It is a highly desired date and types even today.</p>
"
]
,
[
    "category_id" => 10,
    "description" => "<p>After two mediocre attempts at creating a copper coinage for America, the United States Mint finally got it right in the eyes of the public. This was the third major design change within the same year on the one-cent coin. There is some major disagreement and controversy as to who was the responsible party who actually designed the coin.</p>

    <p>Some records name Robert Scot, the Chief Engraver of the Mint; while other records claim that Joseph Wright, who was to be named the Chief Engraver, designed and engraved the dies for the Liberty Cap Cent. Wright was George Washington’s choice to be Chief Engraver. However, we do know for a fact that Wright died in 1793 in Philadelphia from the Yellow Fever epidemic that was sweeping our then-Capitol. Other numismatic historians believe that Henry Voigt, whose first two attempts at a copper coinage were wildly unsuccessful was actually the designer, but given the style of the Liberty Cap Cent, it seems unlikely.</p>

    <p>We do, however, know that Wright did engrave the coin before his death. And it is very likely that several designers made attempts at refining the style of the coin, as we have numerous changes to the dies resulting in different varieties of this coin.</p>

    <p>The style of the coin is more sophisticated than in previous attempts. Miss Liberty faces right, a Phrygian cap, or slave cap, is on a pole behind her head. The Phrygian cap is one originated in Greek times. Newly freed slaves would wear one as a symbol of their freedom, but allegorically it represented Freedom of Thought. Above her head is the word “LIBERTY” and below is the date “1793.” This cap on a pole also symbolized the French Revolution and Thomas Jefferson was a strong proponent of French citizens overthrowing their monarchy just as we had done.</p>

    <p>The reverse has a laurel wreath surrounding the denomination “ONE CENT” at the center of the coin. The Wreath is much thinner and more realistic looking than the Wreath Variety that followed the Chain Cents. Above the wreath and surmounting it are the words “UNITED STATES OF AMERICA” and at the very bottom of the wreath is the numerical expression of the value “1/100.”</p>

    <p>The edge is lettered with “ONE HUNDRED FOR A DOLLAR.” The planchets that were used for striking the later issues (1795 on) were thinner than the earlier issues due to the rolling mills that were used to make the planchets and that made the edge lettering impossible so it stopped with the 1795 issue.</p>

    <p>This design was successful with the public as it was first minted in 1793 but it continued through 1797 when it was replaced in 1798 by the Draped Bust type.</p>

    <p>After the 1793 issue had been released to the public, the striking of large cents resumed in January of 1794 using dies engraved by Scot, They used the same head of Miss Liberty as designed by Wright for the 1793 coins. These were the rare “Head of 1793” coins which are a rare variety of Liberty Cap cents.</p>

    <p>Scot engraved his style of Miss Liberty for the cents bearing the 1794 date but with numerous minor changes to both sides.</p>

    <p>There were a total of four different Liberty Cap cent varieties for 1793. In 1794 there were 56 varieties in addition to several that are so rare they are considered “Non-Collectible.” There were 8 varieties of the 1795-dated coins. Coins of May to June 1796 were struck from dies once again cut by Scot.</p>

    <p>There are over one hundred and twenty different varieties of Liberty Cap Cents as the coin dies used to produce these coins were continually modified. The shape and style of Miss Liberty’s head as well as the numerals in the date were continually refined.</p>

    <p>The United States Large Cents attracted an incredible and large audience of devoted collectors due to the works of Dr. William Sheldon, who devised a scale for grading and thereby valuing Large Cent coins.  His work, “Penny Whimsy” is still the most important work on the subject today and his “Sheldon Numbers” are still used to identify the varieties. In fact, Dr. Sheldon’s grading scale has now been applied to the grading of all US and World coins.</p>
"
]
,

[
    "category_id" => 11,
    "description" => "<p>The Flying Eagle penny lasted for only two short years as a circulating coin, but its influence on U.S. coinage is significant. It’s a highly collectible coin that marked a shift in philosophy for the U.S. Mint and a change in America’s relationship with its own coins.</p>

    <p>Before the Flying Eagle cent, cents were larger, and they often circulated next to coinage from other countries that were used for small purchases as legal tender the same way American cents were. That began to change around the time this cent was minted.</p>

    <p><strong>Flying Eagle Design</strong></p>

    <p>The old Large Cents had risen in price due to the rise in Copper cost and distribution cost. Cents and Half Cents had circulation issues, and outside of larger cities they were rarely seen. The 1850s saw a revamp of low-value subsidiary Silver coinage, which necessitated a change in Copper coinage as well.</p>

    <p>Mint Director James Snowden had been advocating smaller bronze cents and the elimination of the Half Cent since at least 1854, and there were several pattern coins of this type struck around that time. The Mint had been experimenting with designs for a few years, and some had designs similar to those created by late chief engraver Christian Gobrecht some years before. The flying eagle design held particular appeal.</p>

    <p>James B. Longacre designed the new cent based on Gobrecht’s flying eagle and it was struck starting in 1856 as a pattern coin, then from 1857 through 1858 as a circulation strike. Some of the 1856 cents were also struck as proofs for collectors. Very few of these cents exist, as there were only two to three thousand minted.</p>

    <p>This cent’s obverse image of the flying eagle was admired by many, including Augustus Saint-Gaudens, best known for his own flying eagle design for the double eagle coin. Saint-Gaudens called it “the best design on any American coin”.</p>

    <p>The hard cupro-nickel alloy caused major breakage problems for the machines, and many of these cents have weaknesses and were not struck as well as they could have been. The shallower relief in 1858 was used in an effort to address this, but the way the obverse and reverse lined up made it hard to get a good strike because key points of the design were directly opposite each other. In 1859 Longacre designed a new cent, the Indian Head, and the Flying Eagle penny was phased out.</p>

    <p><strong>Historical Significance</strong></p>

    <p>When the cent was issued officially in 1857, it coincided with the Coinage Act going into force. This outlawed foreign and private coinage that had been circulating instead of U.S. Mint coinage and made it possible to redeem the old Spanish coins that were often being used instead.</p>

    <p>As Spanish coins were turned in, the cents glutted the economy, in some places being refused in trade, although this was brief as people turned to coin hoarding in the aftermath of the Civil War. Some were redeemed later on through the Treasury Department, and they became a rarity.</p>

    <p><strong>Numismatic Value</strong></p>

    <p>Though these had very large initial mintages (particularly 1858), the short time they were issued makes them very collectible. And the 1856 pattern coins have a huge premium due to their varieties and the very low mintage.</p>

    <p>Expand your collection today and shop our assortment of Flying Eagle Pennies (1856-1858).</p>
"
]
,

[
    "category_id" => 12,
    "description" => "<p>Old American cents were about the size of a half-dollar, but the small cent that began with the Flying Eagle cent changed that. The Indian Head cent replaced the short-lived Flying Eagle Cent, becoming the first small cent design to run for any length of time.</p>

    <p>The production difficulties caused by the Flying Eagle cent made the Indian Head penny a necessity. After the Civil War, this design became extremely popular, and with coin-operated machines becoming widespread, the cent saw unprecedented demand.</p>

    <p><strong>Indian Head Cent Design</strong></p>

    <p>The Indian Head cent was a replacement for the Flying Eagle Cent. Though the Flying Eagle was considered one of the more beautiful coin designs that had been created for U.S. coinage, it was hard to strike well and many of the coins had weaknesses. As a result, there were only two years of circulation strikes.</p>

    <p>Mint Director James Ross Snowden asked the Philadelphia Mint’s chief engraver James Barton Longacre to design a new cent to replace the Flying Eagle, and the Indian Head design was chosen due to its ease of striking. The low relief of the Indian head and laurel wreath on the back (later replaced by an oak wreath and shield) was far easier to work with than the previous design.</p>

    <p>The head on the coin is the head of Liberty, but Longacre adds a Native American headdress to the traditional Liberty designs. Referencing older designs, he wrote a letter to the Mint Director James Snowden explaining his rationale. “From the copper shores of Lake Superior to the Silver mountains of Potosi from the Ojibwa to the Aramanian, the feathered tiara is as characteristic of the primitive races of our hemisphere, as the turban is of the Asiatic,” stated Longacre. “Nor is there anything in its decorative character, repulsive to the association of Liberty … It is more appropriate than the Phrygian cap, the emblem rather of the emancipated slave than of the independent freeman, of those who are able to say ‘we were never in bondage to any man’. I regard then this emblem of America as a proper and well-defined portion of our national inheritance; and having now the opportunity of consecrating it as a memorial of Liberty, ‘our Liberty’, American Liberty; why not use it? One more graceful can scarcely be devised. We have only to determine that it shall be appropriate, and all the world outside of us cannot wrest it from us.”</p>

    <p>These cents were struck from the same 88% Copper and 12% nickel cupro-nickel alloy as the earlier Flying Eagle cent, and though that particular coin was short-lived it paved the way for the much more popular Indian Head. After 1864 the alloy changed to French bronze (95% Copper with a mix of tin and zinc).</p>

    <p>At the time of the Civil War, U.S. coinage became very scarce as people began to hoard it. Silver coinage disappeared first. Then the cent began to disappear as merchants hoarded them. Premiums for cents rose, and many merchants began issuing their own bronze tokens. The bronze cent came about because of a few factors. First was the popularity of the bronze tokens. In addition, a shortage of imported nickel made it hard to get enough for the planchets. Finally, the nickel in the coins made them much harder than a high-Copper alloy, which meant more wear on dies and machines and a higher likelihood of bad strikes. The bronze cent came into being partway through 1864, with some cents that year being struck from cupro-nickel and some struck from bronze.</p>

    <p><strong>Historical Significance</strong></p>

    <p>These cents were among the most commonly circulated coins in the second half of the 19th century and the first few years of the 20th. Those cents were commonly collected starting in the 1930s but were not widely studied until later when they began to phase out of pocket change. Even early editions of the Red Book had errors related to this cent, as few numismatists had spent the time to get familiar with them.</p>

    <p>That has changed today. Now, these cents are recognized as pivotal examples of American coinage, and they are commonly collected and studied.</p>

    <p><strong>Numismatic Value</strong></p>

    <p>Indian Head cents were struck in huge quantities, with 1907 actually surpassing 100 million. That makes these pennies easy to get into collecting, particularly in lower grades. Even high grades are relatively inexpensive. There are quite a few better date Indian head cents which include the highly counterfeited 1877 as well as the last year of issue, the 1909-S.</p>

    <p>Expand your collection today and shop our assortment of Indian Head Pennies (1859-1909).</p>
"
]
,
[
    "category_id" => 13,
    "description" => "<p>Old Am "]

    ,
    [
        "category_id" => 14,
        "description" => "<p>Old Am "]
        ,
        [
            "category_id" => 15,
            "description" => "<p>The Lincoln Cent was minted for the first time in 1909, and the obverse of the coin is still in use on the cent to the present day. These coins have a classic design and though the reverse has changed, these cents are among the longest-running designs in U.S. coinage.</p>
        
            <p>Many numismatists today got their start with the first iteration of the Lincoln Cent. Today it is still occasionally possible to find the wheat cent in pocket change, though it is rarer than it used to be.</p>
        
            <p><strong>Lincoln Cent Design</strong></p>
        
            <p>The Lincoln Cent was first commissioned to commemorate the 100th anniversary of Abraham Lincoln’s birth. These cents have the obverse of Lincoln’s head, and the original reverse was a pair of wheat ears flanking the denomination.</p>
        
            <p>These early half cents were made with handmade dies, and each die was unique. These issues had problems with the chain motif showing through on the other side as well as the problems that come with individually-made dies like parts of the design cut off, lines in the strike and other errors.</p>
        
            <p>These coins came about because of Theodore Roosevelt’s desire to redesign U.S. coinage. If the coins had been designed less than 25 years before, it would have required an act of Congress to change them. But since the cent, the quarter eagle, the half eagle, the eagle and the double eagle had been in existence for more than 25 years, they were eligible for a redesign based on only the authorization of the Secretary of the Treasury.</p>
        
            <p>Roosevelt complained in 1904 that U.S. coins were not aesthetically pleasing, and the mint hired sculptor Augustus Saint-Gaudens to come up with new designs. Saint-Gaudens was extremely sick at that point, and his assistants carried out the bulk of the work. Saint-Gaudens died in 1907 before completing the design of the cent. Sculptor Victor David Brenner was commissioned to create a new obverse for the cent in 1908. Brenner’s design bears a significant resemblance to an earlier plaque of Abraham Lincoln he had created.</p>
        
            <p>Brenner’s first obverse designs drew inspiration from French Silver coins of the time, but Frank Leach, the Mint Director at the time, objected to the design. Brenner quickly created a reverse with the motto “E Pluribus Unum” and two ears of wheat.</p>
        
            <p>Early pattern coins had Lincoln’s head slightly higher on the obverse, but the circulation strikes approved by President William Howard Taft had the head moved slightly down with the motto “In God We Trust” at the top of the coin.</p>
        
            <p>Victor Brenner’s initials “V.D.B.” were placed at the bottom of the coin on the initial 1909 strikes, but after opposition, the initials were removed from the coin. It was determined that the placement and size of his initials were too much a part of the design. Brenner’s initials were not put back onto the coin until 1918 when they were placed on the base of Lincoln’s bust on the obverse, where they still remain today. Additionally, in an unrelated matter, the vending machine lobby complained that the new cents would not work in their machines, but eventually acquiesced.</p>
        
            <p>These cents were originally struck in the same French bronze as the earlier Indian Head penny. This composition briefly changed in 1943 due to shortages and they were struck from steel, with a few errors in bronze and silver. From 1944 to 1946, cartridge cases were melted and used for coinage, and the composition changed to 95% Copper and 5% zinc. It then resumed its former French bronze composition until 1962 when it changed back to 95% Copper and 5% zinc. In 1982 the cent changed to its current copper-plated zinc.</p>
        
            <p>Most of the cents that are commonly collected are the wheat reverse, often called the “wheat cent” or “wheat penny”. This series ran until 1959 when it was replaced with the Lincoln Memorial reverse. The current cent has replaced this design with a shield in 2010 after a bicentennial quarterly reverse release in 2009.</p>
        
            <p><strong>Historical Significance</strong></p>
        
            <p>The Lincoln Cent was the first usage of a President’s face on commonly circulating U.S. coinage. These paved the way for much of our current coinage. Well-known chief engraver Charles Barber loudly opposed the redesign, but to no avail. It set a precedent.</p>
        
            <p><strong>Numismatic Value</strong></p>
        
            <p>Most Lincoln Cent collectors collect pre-1959 wheat cents. Most wheat cents are common and not very expensive, with the exception of error cents and a few key dates. The early V.D.B. cents with the San Francisco mint mark are considered the king of the key date Lincoln cents, while the 1914-D, the 1922 Plain (struck in Denver), and the 1931-S are also popular key date coins among collectors.</p>
        "
        ]
,
[
    "category_id" => 16,
    "description" => "<p>The U.S. Three-cent nickel was minted from 1865 to 1889 and slightly overlapped with the Three-cent Silver piece. The Three-cent Silver piece came about due to the decrease in postage rates, which dropped from five cents to three. This made it efficient to mint a smaller denomination coin that could handle the transaction easily. The nickel was part of a bill that introduced new base metal coinage to American money, and it ran alongside the much more popular five-cent nickel which we still have today.</p>

    <p><strong>Three Cent Nickel Design</strong></p>

    <p>The Three-cent nickel was made of 75% copper and 25% nickel, the same ratio used in today’s five-cent nickels. This composition has remained the same for all nickels with the exception of the Silver alloy war nickels (from 1942-1945), regardless of denomination or series.</p>

    <p>Joseph Wharton was a magnate who controlled much of the nickel mining in the United States at the time. Wharton successfully lobbied Congress for the use of nickel in U.S. coinage. The U.S. Mint had been skeptical of nickel coins, but the three-cent coin was successful enough to pave the way for other denominations, and Wharton’s supporters won the five-cent nickel in 1866 after the successful first year of the three-cent nickel.</p>

    <p>The planchets for nickel coins were very hard — harder than the Silver, Gold and Copper that they were replacing. This made striking difficult. Even with simple designs, they were prone to bad strikes.</p>

    <p>The obverse of the Three-cent nickel had a Liberty Head design created by James Barton Longacre, and the reverse was the Roman numeral of the denomination. Mintage declined over the course of its run and the change in postage put the final nail in this coin’s coffin, with its last issue coming in 1889.</p>

    <p><strong>Historical Significance</strong></p>

    <p>Postage changes drove down the need for the Three-cent Silver and the later Three-cent nickel, and a variety of other factors caused its demise. Though the nickel lasted longer than the Silver coin, it died out around the time that postage changed from three cents to two. Three-cent nickels were made for a few more years but were no longer struck after the 1889 issue. Though it was more successful than the paper fractional currency notes, it never surpassed the popularity of the cent and five-cent pieces.</p>

    <p><strong>Numismatic Value</strong></p>

    <p>Three-cent nickels are much more common in dates of 1876 and earlier. After that point, most mintages were very small (with the exception of one anomalous massive year in 1881). Premium quality 1883, 1884, and 1885 coins command a significant premium, and these are not common.</p>

    <p>Expand your collection today and shop our assortment of Three Cent Nickels.</p>"
]

    ,
    [
        "category_id" => 17,
        "description" => "<p>Old Am "]
        ,
        [
            "category_id" => 18,
            "description" => "<p>The Two-Cent Piece had a couple of false starts before it was first struck in 1864. These coins were only minted for a short time, largely due to a shortage in U.S. coinage from Civil War hoarding. Two-cent pieces were an emergency measure due to a need to get as much dollar value as possible out in coinage, as they were a size and type that was workable with current dies and equipment. The cost and speed of creating a Two-cent piece were comparable to that of creating Cents, so doubling the value was more efficient.</p>
        
            <p>These coins only ran through 1873. There are a few earlier pattern coins for 1863 struck from the same bronze planchets as the final design, and there was an abortive attempt at striking Two-cent pieces in 1836. A few of these pattern coins in various metals survive but they are all one of a kind.</p>
        
            <p><strong>Two Cent Piece Design</strong></p>
        
            <p>The Coinage Act Abraham Lincoln signed on April 22, 1864, changed the Cent, but it also made provision for a new bronze Two-cent piece. These small Cents and Two-cent pieces were meant to replace many of the private-cent tokens that had been circulating, and they marked a change in philosophy for U.S. coinage.</p>
        
            <p>Previous to this point all coins had been struck with the expectation that they would have a certain amount of intrinsic value due to their metal content. But the popular circulation of private tokens that had a very small amount of intrinsic value showed that people would still use coins that had less intrinsic value if they trusted the issuing authority. Bronze coins would not contain their face value in metal, and the striking of bronze coins indicated that the U.S. Mint was ready to move on from its previous philosophy.</p>
        
            <p>The Two-cent coins were among the first to be able to incorporate a motto and change designs at the Mint Director’s discretion, so long as he had the Secretary of the Treasury’s approval. As a result, they were the first U.S. coin to include the motto “In God We Trust”, today stuck on all U.S. coinage. The motto went through a few iterations before it was actually added to the coin, with “God Our Trust” and “God and Our Country” being among the suggestions which we can still see on pattern coins.</p>
        
            <p>The design was a simple shield motif on the obverse with a wheat wreath surrounding the denomination on the reverse. The design was produced by James B. Longacre, and it draws obvious inspiration from the Great Seal of the U.S.</p>
        
            <p><strong>Historical Significance</strong></p>
        
            <p>These coins and the small Cents of the same period were the first base metal coins to not contain their intrinsic value in metal. They set a precedent that has carried down to this day. Demand for the coins was so large that they had to increase production. They were extremely popular in their time but rapidly dropped off in mintages. After the Civil War, the minting of the new five-cent Nickel coin made these an unnecessary denomination. They fell out of favor and stopped being struck by 1873 when the Mint Act ended them. The 1873 coin only exists in proof.</p>
        
            <p><strong>Numismatic Value</strong></p>
        
            <p>In lower grades, almost all of these coins are very affordable. There are a few exceptions including the 1864 small motto and the 1872 issue (due to its small mintage). These coins command a significant premium.</p>
        
            <p>Many of the Two-cent pieces issued were recalled to the Treasury for melting into one-cent pieces. Few numismatists will collect the two-cent piece as a series, instead, they collect it as part of a type set. Though the overall surviving population is not huge, demand has kept prices relatively low for much of the series.</p>
        
            <p>Expand your collection today and shop our assortment of Two Cent Pieces (1864-1873).</p>
        "
        ]
,        
[
    "category_id" => 19,
    "description" => "<p>Learn About the History of Buffalo Nickels</p>
    
    <p>The Buffalo Nickel or Indian Head Nickel as it was sometimes known is perhaps the most iconic of American coins. With a Native American on the obverse and an American Bison on the reverse, the coin truly captures the spirit of the American West. Issued between 1913 and 1938 it was designed by famed sculptor James Earle Fraser.</p>
 
    <p>(Sculptor James Earle Fraser, a student of Augustus Saint-Gaudens.)</p>
    
    <p>Following in the great tradition of Augustus Saint-Gaudens and President Teddy Roosevelt, his successor, President William Howard Taft asked Fraser in 1911 to submit a new design for Charles Barber’s Liberty Head Nickel. By 1912, Fraser had submitted several designs with a similar theme – a Native American on the obverse and an American Bison on the reverse. The design was much heralded.</p>
    
    <p>(Fraser’s Original Plaster Models for the Buffalo Nickel – obverse [left] and reverse [right].)</p>
    
    <p>Fraser was awarded $2,500 (Approximately $75,000 today) for his efforts. Minting began, after a slew of minor tweaks and modifications, in 1913. The coins were released into circulation on March 4, 1913, and were lauded by the public but not so by the media. The New York Times printed an editorial that stated “New nickel is a striking example of what a coin intended for wide circulation should not be….. it is not pleasing to look at when new and shiny, and will be an abomination when old and dull.” The NYT editorial completely missed the mark on the public reaction. It is a succinctly American design – Native American Chief on the obverse and an American Bison, which once dominated the Plains, on the reverse.</p>
 
    <p>(The 1913 Buffalo Nickel, Type I – obverse [left] and reverse [right}.)</p>
    
    <p>But as the coins soon entered circulation Treasury officials noted that changes needed to be made. The date and the denomination (FIVE CENTS), soon wore off the coin with increased circulation. The changes that Barber suggested to Fraser and the Treasury officials were to make the numerals in the date on the obverse wider. In addition, the legend “FIVE CENTS” was enlarged. Finally, one additional change was the bison went from standing on a mound to standing on flat ground with a line above the legend “FIVE CENTS” – all of which were intended to keep the date and legends visible for a much longer period of circulation.</p>
    
    <p>Striking these coins was difficult anyway with the metallic composition of .750 Copper and .250 Nickel and having such a complex die to fill. Fraser’s work had intricate designs on the Indian’s hair, braid and feathers. Likewise the Buffalo’s horn, cape and tail were difficult at best to strike up. But as bad as it was for the Philadelphia Mint to accomplish these strikings, the branch mints in Denver and San Francisco had a much more difficult time. Coins dated in the 1920s and struck at these branch mints are notoriously weakly struck.</p>
 
    <p>(The 1913 Buffalo Nickel, Type II, with wider and stronger date, mound removed, bar above the denomination and a wider and stronger denomination – obverse [left] and reverse [right].)</p>
    
    <p>Fraser’s design, in spite of how the New York Times described it, accomplished EXACTLY what Fraser set out to do. He wanted a coin that was uniquely American and the American Bison was the master of the Plains and the Native American complimented the design. Fraser had the Native American chief – actually a composite of at least 4 Native American chiefs that he sketched- facing right, with the motto “LIBERTY” above and the date below him comprising the obverse elements. The reverse had the American Bison facing left, with the legends “UNITED STATES OF AMERICA” above him and the denomination “FIVE CENTS” below him. The bison model that he used was either the popularly-believed “Black Diamond” who lived at the Central Park Zoo or “Bronx” who did live at the Bronx Zoo.</p>
    
    <p>The 1913 Type I and Type II Nickels each had around 30 million coins struck at the Philadelphia Mint.  Both 1913-D coins each had 4 to 5 million struck while each type of S mint had 1 to 2 million minted. 1914 had more than 20 million minted and also produced an overdate 1914/3. 1914 D and S coins through 1919 D and S coins are all better dates.</p>
    
    <p>A rarity was created when a 1916/1916 Doubled Date Obverse was minted. Additionally, there is a 1918/7-D that is one of the keys to the Buffalo Nickel series.</p>
    
    <p>(1916 Over 16 Doubled Die Obverse [left] and the 1918/7-D Overdate Obverse [right]).</p>
    
    <p>Starting with 1920 and continuing to 1926-D, all dates and mintmarks are slightly better with the exceptions being the 1921-S and 1926-S which are scarce dates and worth considerably more than the other early 1920’s dates and mintmarks.</p>
    
    <p>The remaining dates and mintmarks from 1927 through 1938-D are all common dates. The exceptions to that run of dates and mintmarks are the 1935 Double Die Reverse, the 1936-D with 3 ½ legs on the Buffalo, and the 1937-D with the rare and well-known 3-Legged Buffalo, all of these three varieties are RARE!</p>
    
    <p>(The RARE 1937-D 3-Legged Buffalo Nickel, missing the right front leg.)</p>
    
    <p>A pressman at the Denver Mint caused the 1937-D 3-Legged Buffalo error to be struck. He was trying to remove marks on the reverse die that he was using to strike coins and in filing the marks down completely eliminated Buffalo’s right front leg, creating the rarity.</p>"
]
,
[
    "category_id" => 20,
    "description" => "<p>Learn About the History of Jefferson Nickels</p>

    <p>Our beloved Jefferson Nickel has been minted since 1938. As the 25th year of minting the hard-to-strike Buffalo Nickel was approaching, the Mint asked repeatedly for a new design to strike. Once approved by Treasury officials, the Mint created a design competition. The obverse of the coin should honor Thomas Jefferson and the reverse should depict his home in Virginia – Monticello.</p>

    <p>In January of 1938, the US Mint announced a competition to design the new Jefferson Nickel. The obverse of the coin would depict a bust of Thomas Jefferson while the reverse would picture his home in Virginia – Monticello. By April, the competition had closed and nearly 400 entries had been submitted. German sculptor, Felix Schlag, who had immigrated to the United States in 1929, was the winner of the competition and the $1,000 prize.</p>

    <p>(German Sculptor Felix Oscar Schlag)</p>

    <p>(Felix Schlag’s entry into the 1938 Jefferson Nickel competition.)</p>

    <p>The Mint required some additional changes such as a more refined and longer portrait view of Jefferson, removal of the tree on the reverse as well as the improper placement of the name of Jefferson’s home and Schlag complied by June and the final designs accepted in July.</p>

    <p>The final design had a portrait of Jefferson facing left with the motto “IN GOD WE TRUST” on the left side and “LIBERTY” and the date separated by a star on the right side of the obverse. The reverse had a depiction of Monticello as the central theme on the reverse with “E PLURIBUS UNUM” above it and “MONTICELLO” below it with the denomination “FIVE CENTS” below that and finally “UNITED STATES OF AMERICA” at the bottom.”</p>

    <p>(The 1938 Jefferson Nickel, obverse [left] and reverse [right].)</p>

    <p>Production began in September 1938, at all three then-operating mints – Philadelphia, Denver and San Francisco. Philadelphia coined 19.5 million coins, Denver 5.4 million and San Francisco 4.1 million of these first-year coins. The composition was unchanged from the prior Buffalo Nickel at .750 Copper and .250 Nickel. With so few coins available they were hoarded by collectors and dealers alike and few saw circulation.</p>

    <p>In order to remedy that situation, the Philadelphia Mint alone struck over 120.6 million 1939-dated nickels with the two branch mints contributing another 10 million coins combined. But in Philadelphia’s haste to strike coins and alleviate the hoarding, they created a rarity with the doubling on the reverse of the words “MONTICELLO” and “FIVE CENTS.”</p>

    <p>From 1940 to the 1942-D coins, the Mint struck tens to hundreds of millions of these coins. But in October of 1942, Nickel was declared a “necessary and needed wartime material” and the composition of the coin was changed to .560 Copper, .350 Silver and 0.90 Manganese. Although Silver was a valuable precious metal, Nickel was needed for the war effort and 1942-dated coins were struck at Philadelphia and San Francisco. To be able to identify these new coins, a mintmark was used for the first time “P” for coins struck in Philadelphia. The mintmarks for these special “silver nickels” was enlarged and placed over Monticello for easy identification.</p>

    <p>(The Silver War Nickels with their easy-to-see Mintmarks – P, D & S.)</p>

    <p>Even within the short-lived war nickel series, there were three varieties worthy of note – 1943-P 3 Over 2 Overdate, a 1943-P with a Doubled Eye on Jefferson and 1 1945-P with a Doubled Die on the reverse. The highest mintage war nickel was the 1943-P with over 271 million coins struck and the lowest mintage was the 1943-D with just over 15 million coins struck.</p>

    <p>From 1946 to 1950, only two coins are worthy of mention. In 1949 the Denver Mint struck their 1949-D coin using a die intended for San Francisco. Thus the 1949-D Over S overdate was born. In 1950, also at the Denver Mint, only 2.6 million coins were struck and that low number created an incredible demand for those coins. This coin is worth about $15 to $20 in Mint State today, yet was bringing close to $50 per coin in the 1960s. It was one of the first modern rarities and the chance to find one in circulation created hundreds of thousands of new coin collectors.</p>

    <p>Two additional overdates between 1951 and 1964 exist – 1954-S Over D mintmark and the 1955-D over S mintmarked coins are the two examples. Between 1965 and 1993, the only coin of note and worth a premium is the 1971 (No S) Proof Jefferson Nickel. The San Francisco Mint began issuing Proof Specimens in 1968 but in 1971 a small number of proof coins were struck without the S mintmark. Proof coins had been struck since the coin’s initial minting in 1938.</p>

    <p>From 1994 to 2003, only two rarities emerged. In 1994, there was a special Philadelphia striking that was frosted like a Proof coin but was itself struck as a Mint State coin. It accompanied the 1993 Jefferson Commemorative Silver Dollar and, again, in 1997 there was a special Philadelphia frosted Proof-looking Mint State coin that was issued with the 1997 Botanic Gardens commemorative coins.</p>

    <p>The remaining issues from 1998 to 2003 were uneventful. In 2004 and 2005 to celebrate the Bicentennial of the Louisiana Purchase, the coins were redesigned on the obverse with a stylized portrait of Jefferson on the obverse and 4 completely new designs on the reverses. None of these coins, due to the enormous numbers minted, are even scarce in price. The coins minted in 2006 to date kept the stylized obverse portrait of Jefferson but returned to the same reverse first used in 1938. Again, none of the coins are valuable.</p>"
]
,
[
    "category_id" => 21,
    "description" => "<p>The First Nickel Not Made of Silver – Shield Nickels</p>

    <p>The Shield Nickel is the first five-cent coin issued by the United States that was not made of silver. The US Mint first issued silver half dimes back in 1794. Silver Half Dimes were struck from that date until 1873. But the Shield Nickel was struck out of copper and nickel. It was designed by James B. Longacre, the Chief Engraver of the US mint in 1866.  Longacre based his design on the reasonably successful Two Cent Piece he also designed in 1864.</p>

    <p>(The Two Cent Piece [left] and the Shield Nickel [right] were both designed by Longacre.)</p>

    <p>Now that the Civil War was a very recent memory, Longacre wanted a coin that would symbolize American unity. He chose to take elements from his Two Cent piece and incorporate them here. He needed a quick design and the Two Cent piece provided one.</p>

    <p>Longacre redirected the two upwards pointing arrows behind the Two Cent piece shield to now become two downward pointing arrows behind the shield. He also removed the scroll above the shield on the Two Cent piece and added those same words (IN GOD WE TRUST) on the scroll now as a motto above this modified shield.  He also added a cross to the top of the shield to honor the dead on both sides of the Civil War. Although Longacre also created a pattern for the Shield Nickel that displays an obverse portrait of recently assassinated President Abraham Lincoln. While there was a drumbeat in the North to honor the slain President, Treasury officials felt that the coin would not be welcomed in the South and that it would reopen very fresh wounds. It was not shown to the Treasury Secretary so there could not be any official consideration of the design.</p>

    <p>Longacre’s obverse design is “one of the most patriotic motifs in American coinage” as described by Q. David Bowers. It is based on the coat of arms of the Great Seal of the United States.</p>

    <p>The reverse depicted the numeral “5” in the center surrounded by 13 stars and between each star was a ray design. Above the stars were the words “UNITED STATES OF AMERICA” and the word “CENTS” was at the bottom under the numeral.</p>

    <p>(The 1866 Shield Nickel with rays design [left] and the without rays design of 1867 and later [right])</p>

    <p>The coins were hard to fully strike up due to the design and the fact that the copper-nickel metal mix was harder caused the dies to begin to break down that much more quickly. In 1867 the reverse was redesigned to eliminate the rays which the hope was to making the coins easier to strike. But the with rays design also reminded some of the Southern “Stars and Bars” and that was totally unacceptable just two short years after the war had ended.</p>

    <p>The first year, 1866, produced 14,742,500 coins with the rays reverse. The next year, 1867, only 2,019,000 coins were struck with the rays reverse and 28,890,500 coins were struck with the new modified ‘no rays’ reverse.</p>

    <p>(Longacre continued to experiment with designs. Here is an 1867 Shield Nickel pattern in copper, which was much easier to strike.)</p>

    <p>From 1868 to 1870, the mintages rapidly declined from nearly 29 million down to 4.8 million coins struck. In 1871 only 561,000 were struck as there was a glut of them on the market. From 1872 until 1876, from a high of 6 million to a low of 436 thousand coins were minted. But 1873, as with numerous other denominations of United States coins saw a “close 3” and an ”open 3” as major varieties. That was, however, the last year for the production of the silver Half Dime, so the nickel was not the only coin denomination between the three-cent piece and the dime.</p>

    <p>Both 1877 and 1878 were proof coins only years with 800 and 2,350 struck respectively.  The year 1879 had a normal date and a scarce 1879 over 8 overdate. 1880 saw only 16,000 coins struck and is pretty rare. In 1881 68,800 coins were minted. 1882 was the largest production year since 1869 with 11,472,800 coins produced. The final year, 1883, saw a normal date and an overdate 1882/3 and there were 1,451,500 coins struck.</p>"
]
,
[
    "category_id" => 22,
    "description" => "<p>Learn About Charles Barber’s Liberty Head Nickel</p>

    <p>The Liberty Head Nickel was minted between the years 1883 and 1912. Some people even include the year 1913 as a year of mintage but that is due to the tiny numbers of 1913-dated coins in existence. The Chief Engraver for the US Mint Charles E. Barber was asked to design the one-cent, three-cent and five-cent pieces, which he did, but only the five-cent design was adopted.</p>

    <p>Replacing the style-less Shield Nickel would not pose that difficult a task. Barber designed a coin with the head of Miss Liberty facing left with “UNITED STATES OF AMERICA” around her and the date below on the obverse. The reverse of his design had a large Roman “V” for five in the center of a wreath with “E PLURIBUS UNUM” above it.</p>

    <p>(1882 Pattern by Charles E. Barber for the new Liberty Head Nickel.)</p>

    <p>The accepted design was not too radically different from Barber’s pattern piece. Miss Liberty still adorned the obverse and faced left. The date was still below her, but she was surmounted by 13 stars instead. The Legend “UNITED STATES OF AMERICA” was moved to the reverse of the coin.</p>

    <p>(The 1883 Liberty Nickel – approved dies.)</p>

    <p>There was just one small, minor problem. Some enterprising individuals decided to fool the unsuspecting public with the new coin. So what was the problem? Well, the coin could be gold-plated and passed on to the public as a $5 Gold piece. And that is exactly what happened.</p>

    <p>There is a legend associated with this coin, as follows. An adventurous young man, who also happened to be a deaf-mute, by the name of Josh Tatum made quite a living off of this coin. He was from Boston and bought 1,000 of these new coins and he asked a jeweler to gold plate them for him. Josh would go into a local bar or general store and point to something that cost 5 cents, such as a cigar. He would then produce one of his “gold-plated nickels” and give it to the bartender or proprietor. Most would assume that he had just given them a $5.00 gold coin so he would get his beer, cigar or merchandise as well as $4.95 in change. He never said a word – because he couldn’t.</p>

    <p>Eventually, the newspapers carried woodcuts or other images of the new nickel coin – and Josh was caught. He went to court and his lawyer made a great case on his behalf. As a deaf-mute, he never told anyone that what he gave them wasn’t a nickel. His defense was, unbelievably, that they made the mistake and overpaid him. A judge agreed and Josh was freed. That is, supposedly, where the expression “are you joshing me?” originated. Whether it is true or legend, in 1883 the Mint changed the design on the reverse and added the word “CENTS.”</p>

    <p>(The original 1883 Liberty Head Nickel reverse [left] without CENTS. The modified 1883 Liberty Head Nickel reverse [right] with the word CENTS added.)</p>

    <p>So 1883, the first year of production saw 5,474,300 coins produced without the word CENTS on the reverse and 16,026,200 coins produced later that year with the word CENTS added to the reverse. The next year, 1884, saw 11,270,000 coins struck. The following year, 1885, saw the smallest production of business strike coins – only 1,472,700 coins were struck. And 1886 was another year in which a limited mintage was struck – only 3,326,000 coins bear that date.</p>

    <p>Production cranked up in 1887 and through 1911, production years minted a high of 39.6 million coins and a low of only 5.4 million coins. But production at the Philadelphia Mint was regular and all dated years were actually minted.</p>

    <p>Demand in the new century was high for a nickel. Nickel street cars, nickel cigars, and nickel coin-operated machines of all kinds were just becoming very popular. Both merchants and the public clamored for the coins and millions and millions were produced. In fact for the first few years of the 20th Century, the Philadelphia Mint had shifts working 24 hours a day to keep up with the demand for coins.</p>

    <p>In 1912, a startling thing happened. Not only were 26.2 million nickels produced in Philadelphia but 8.4 million were struck in Denver and a mere 238,000 coins were also produced at the San Francisco Mint. The west coast Mint did not start to mint nickels until Christmas Eve and only struck them for four business days. The then-Mayor of San Francisco took one of the first forty 1912-S Liberty Head nickels that were struck and used it to pay the fare on the brand-new streetcar that was just beginning its run then. 1912 is the only year where coins were produced outside of Philadelphia.</p>

    <p>(The 1912-D Liberty Nickel reverse [left] and the 1912-S Liberty Nickel reverse [right].)</p>

    <p>1913 Liberty Head Nickels do NOT exist – at least that is what the official records of the US Mint will state. And even though no 1913 dated Liberty Head nickels, at least 5 of these coins do exist.</p>

    <p>At the 1920 American Numismatic Association’s convention held in Chicago, coin dealer Samuel W. Brown displayed the first 1913 Liberty Head nickel ever to be publicly seen. Several months earlier, Brown had run advertisements to buy any 1913 Liberty Nickels that existed.</p>

    <p>Brown’s story was that before the plans had been made to change the design from the Liberty Head Nickel to the Indian Head nickel, dies had been prepared and several coins had been struck to test the die. Brown actually possessed five specimens of the coin. The coin is today one of the great American coin rarities and specimens command millions of dollars.</p>"
]
,
[
    "category_id" => 23,
    "description" => "<p>There was a 23-year hiatus since the last Draped Bust Half Dime in 1805 and the first new Capped Bust Half Dime in 1829. The coins were designed by the Chief Engraver of the US Mint at that time, William Kneass.</p>

    <p>Due to the rise in the price of silver, the silver fineness remained the same at .8924 but the size of the coin changed from the earlier Draped Bust Half Dime coins (1794 – 1805). The diameter shrunk by 1 millimeter from 16.5 mm to 15.5 mm. Most people could not notice that difference but they did notice immediately that the designs had changed on both sides. The Draped Bust Miss Liberty, facing right, was gone and replaced by yet another Miss Liberty.</p>

    <p>This Miss Liberty seemed a bit older, wore her hair down, but covered most of it inside of a Phrygian cap with the word “LIBERTY” emblazoned across the brow. She has the nickname of a “Capped Bust” though early collectors also called this type of coin the “Turban Head” even though it doesn’t look like a turban. She faced left, instead of right, but. As usual, she was surrounded by 13 stars and had her date below her.  The reverse also was changed completely. Instead of the Heraldic Eagle design, the new American eagle had down-spread wings, a Union shield across her midsection, arrows and olive branches below, a banner above her with “E PLURIBUS UNUM” on it, “UNITED STATES OF AMERICA” above and the denomination expressed as “5 C.” at the bottom.</p>

    <p>These designs are actually updated versions of John Reich’s Capped Bust design on the Half Dollar from 1807. The differences are more minor than major but nothing had been like it on the Half Dime since none had been minted since 1805.</p>

    <p>So in 1829, the Philadelphia Mint struck 1,230,000 coins and in 1830 they struck 1,240,000. In 1831, the mintage figure increased just slightly to 1,242,700 but in 1832 dipped under the 1 million mark at 965,000. 1833 saw 1,370,000 coins struck and 1834 had 1,480,00 coins but also created a variety – 1834 with a 3 over an inverted 3.</p>

    <p>Now the varieties began to become more plentiful. In 1835 a record 2,760,000 coins were struck but they were spread among 4 distinct varieties – none of which are particularly more valuable. The varieties are “Large Date and 5C,” “Large Date and Small 5C,” “Small Date and Large 5C,” and, obviously, a “Small Date and 5C.”</p>

    <p>1836 saw 1,900,000 struck and 3 varieties – “Small 5C,” “Large 5C” and “# over Inverted 3.” The final year, 1837 saw 871,000 coins and both Small and Large 5C varieties.</p>
"],

[
    "category_id" => 24,
    "description" => "<p>The reaction to the “Flowing Hair Half Dime” was not mixed – at all. Seemingly no one, other than Robert Scot and some Mint officials liked the design and so this type of coin was ended after two brief years. What most people objected to was the “Flowing Hair’ on Miss Liberty. Some thought it scary; others thought that it was undignified for our coinage.</p>

    <p>One of the first objectives for the new Director of the US Mint, Henry De Saussure, was to redesign the Flowing Hair coinage. Wanting something radically different from what Robert Scot had previously provided, De Saussure turned to artist Gilbert Stuart. Stuart was becoming the preeminent portrait artist in early Federal America. Stuart’s numerous portraits of General Washington were well known and widely appreciated. Two patrons of artist Stuart were Martha Washington and Mrs. William Bingham of Newport, Rhode Island. Both had ordered two full-length portraits of General Washington for their homes. De Saussure asked Stuart to submit sketches for a new vision of Miss Liberty. Stuart asked his friend and patron Mrs. Bingham to model for Miss Liberty and she did.</p>

    <p>(Artist Gilbert Charles Stuart [left] and Mrs. William Bingham, his model [right].)
Robert Scot and John Eckstein took the sketches of Stuart’s and, using Mrs. Bingham as the model for Miss Liberty, created a different and more appealing coin. Miss Liberty faced right and had the date below, the motto “LIBERTY” above and 7 stars to the right and 8 stars to the left. The reverse was essentially unchanged from the Flowing Hair style with an American eagle holding an olive wreath with “UNITED STATES OF AMERICA” around it.</p>

    <p>(The new Draped Bust Half Dime Style of 1796 and 1797.)
In 1796 only 10,230 coins were produced but that included a normal date, and over date of 1796/5 and a 1796 coin where the letter “B” in “LIBERTY” was actually a defective “R” that looked like a “K”- “LIKERTY.” Then in 1797, a mere 44,527 coins were struck and there are 3 varieties among the stars. The first coins struck had 15 stars, 1 for each state in the Union. Later in the year, Tennessee became a state so a 16th star was added and finally a 13-star variety because the Mint realized it could not strike an attractive and aesthetically balanced coin if they were to keep adding stars to the design. It permanently became 13 stars from that point forward.</p>

    <p>(From left to right – 1796 LIKERTY, 1797 15 Stars. 1797 16 Stars and 1797 13 Stars varieties.)
No Draped Bust Half Dimes were struck between 1798 and 1799 but production resumed in 1800. However, now the reverse American eagle design was in for a major renovation. This created the two major types of Draped Bust Half Dimes – the Small Eagle and the Heraldic Eagle. The Small Eagle (scrawny) was replaced by the Heraldic eagle as its wings are outspread, a shield in front of the eagle, and arrows and an olive branch are in the talons to demonstrate that America was ready for war or peace. Above the eagle are 13 stars with clouds above the stars. “UNITED STATES OF AMERICA” surrounds the design.</p>

    <p>(The new Draped Bust Half Dime reverse design – Heraldic Eagle.)
In 1800 24,000 normal coins and 16,000 of the LIKERTY variety were struck. Production dropped to 27,760 coins in 1801. The year 1802 was a very bad year for the Draped Bust Half Dime as only 3,060 coins were minted due to a silver shortage and production problems. 1803 saw two varieties – a large “8” and a small “8” in the 37,850 coins minted.  No coins were struck dated 1804. In the final year of this design, 1805, 15,600 coins were minted. There were to be no more Half Dimes until 1829.</p>

    <p>Although the half dime was an important coin in the commerce of the country, yellow fever in Philadelphia and poor mint strikings combined to limit the production of these important coins. Expand your collection and shop our array of rare coins and currency today.</p>"
]
,
[
    "category_id" => 25,
    "description" => "<p>The Flowing Hair Half Dime was actually the first silver coin worth five cents but it was the second half dime created. The first of the very limited “Half Disme” of 1792, created especially for George Washington, reportedly used silver from a silver tea service that belonged to Martha. Due to its very limited mintage, the 1792 Half Disme is really a pattern coin and not a regular issue.</p>

    <p>(The 1792 Half Disme pattern coin.)
The Flowing Hair variety of Half Dime was designed by Chief Engraver of the US Mint Robert Scot. Scot used the same exact design on both the half dollar and one dollar silver coins of 1794 and 1795. There is no denomination so the size and weight of the coin determine the denomination. Unlike ALL later silver coins, this issue was comprised of .8924 Fine Silver mixed with .1076 Copper. Remember that the fledgling US mint was still trying to determine the proper “mix” for our country’s silver coinage.</p>

    <p>(The 1794 Flowing Hair Half Dime.)
Scot’s design was simple itself. The obverse had a simple Miss Liberty facial design, looking upward, and facing to the right. Above her was the motto “LIBERTY” and below her was the date “1794.”  She is surrounded by 15 stars – 8 to the left and 7 to the right.</p>
 
    <p>There was an American eagle with wings spread holding a wreath in her talons. The wreath surrounds her and “UNITED STATES OF AMERICA.” The design is beautiful in its simplicity. It is thought that 7,756 of these 1794-dated Half Dimes were actually struck in March of 1795. The Red Book (A Guide Book of United States Coins) does not state an actual mintage figure but simply states that 86,416 of 1794 and 1795 dated coins were struck, according to the records of the US Mint.</p>
 
    <p>(1795 Dated Flowing Hair Half Dime.)
There is no difference in the design at all between the 1794 and 1795 half dimes, except for the date. The strikes of both of these coins tend to be weak or irregular. The Mint was still learning the proper amount of pressure to use on these small, thin silver planchets and that clearly shows in the coins that they produced. As you can see in the above coin (graded MS-62 by PCGS) the breast feathers on the eagle are often missing. Coins are unevenly struck, and dies are often rotated.</p>
 
    <p>Some planchets actually weigh too much and needed to be “adjusted.” These coins have what are called “adjustment marks” on the planchets. Adjustment marks are long straight lines on the coin where a file was drawn across the coin to reduce the amount of silver in the coin itself. Adjustment marks are very common on US silver coins prior to about 1830. They do not reduce the coin’s grade or value on these early specimens.</p>
 
    <p>(A 1795 Flowing Hair Half Dime with Adjustment Marks on both sides.)
The 1795-dated half dimes generally have a slightly better strike than their 1794-dated counterparts. But expect strikes to be uneven, possibly with strong centers and weak designs around the perimeter or vice versa. As the dies were striking more coins, cracks began to appear in the dies themselves and, as expected, on the coins.</p>
 
    <p>Except in the uncirculated grades, this two-year type coin is still affordable to the serious collector and both coins could be purchased for about $10,000. These coins are magnificent pieces of historical Americana and relics of the George Washington presidential administration.</p>"
]
,
[
    "category_id" => 26,
    "description" => "<p>Although Capped Bust Half Dimes were minted in 1837, there was an impetus to change the design again. The task fell to Christian Gobrecht who was the Second (behind Chief) Engraver of the United States Mint. He had worked for the US Mint as early as 1823, but it was a temporary position. Gobrecht finally became the Chief Engraver in 1840 and held the position until his death in 1844.</p>

    <p>(One of the very few portraits of Chief Engraver of the United States Mint, Christian Gobrecht.)
The design was similar to his pattern of Silver dollars of 1835 and 1836 (the Gobrecht Dollars) and was simple in its presentation. The obverse of the coin had Liberty, seated, facing left, holding a shield in one hand and a pole with a cap on the end of it in the other.  The only distraction to that symbolism was the date below Miss Liberty. The reverse had the denomination “HALF DIME” in two lines, enclosed inside a wreath with “UNITED STATES OF AMERICA” around it.</p>

    <p>(This is the VARIETY ONE – No Stars on Obverse – minted in 1837 and 1838.)
The 1837 coin was minted in Philadelphia and 1,405,000 were struck. There were two varieties – a Small Date and a Large Date, neither of them is particularly more valuable than the other. The only other coin issued a Variety One was an 1838-O struck in New Orleans, with only 70,000 coins actually minted. It is considerably rarer than either of the 1837 varieties.</p>

    <p>(This is VARIETY TWO – in 1838 13 stars were added to the obverse on all Philadelphia coins.)
In 1838 the Philadelphia Mint added 13 stars above and around Miss Liberty and this was continued through 1859. The New Orleans “O” mintmark appears above the bow on the reverse. In 1840, a fold of drapery was added from the right elbow on Miss Liberty. Coins were struck in Philadelphia and New Orleans and the design was unchanged until 1853.</p>

    <p>1838 dated coins come in both Large and Small star varieties with the latter being much more valuable. 2,225,000 1838-dated coins were minted in Philadelphia. 1839 saw over 1 million coins struck both at Philadelphia and New Orleans and they are valued similarly.  Philadelphia struck over 1 million coins in 1840 and New Orleans struck under 700,000 coins that same year. The same pattern was repeated in 1841 with Philadelphia over 1 million coins and New Orleans just over 800,000. In 1842 the main mint struck 815,000 and the branch mint only 315,000.  </p>

    <p>No New Orleans coins were struck in 1843, while Philadelphia struck 815,000. In 1844 mintages dropped at both mints with Philadelphia striking 430,000 and New Orleans only striking 220,000 coins. Between 1845 and 1848, coins were only struck in Philadelphia. 1846 was a difficult year with only 27,000 minted making any half dime dated 1846 very scarce in any grade. 1849 saw over 1/3 million coins struck and 3 varieties were created – a normal date, a 9 over 6, and a 9 over a widely spaced 6. In New Orleans 140,000 were struck that same year. In both 1850 and 1851 coins were struck at both mints and the mintages were all under 1 million coins in all instances.</p>

    <p>In 1852 and 1853 coins were once again struck at both mints. In 1852 over 1 million coins were struck at Philadelphia but the other 3 years and mintmarks were 260,000 or less, with the 1853-O being particularly rare in all grades! The 1853 coin has the lowest mintage with only 135,000 coins struck at Philadelphia that year of this particular type.  But a new variety was about to be struck.</p>

    <p>(This is VARIETY THREE – arrows added at the date on the obverse to signal a reduction in the silver weight.)
Later in the year 1853, arrows were placed on each side of the date to denote a reduction in the Silver weight of the coins. This continued from 1853 through 1855 and applied to coins minted both in Philadelphia and New Orleans. 1853 from Philadelphia had the highest mintage at 13.2 million coins while the 1885-O had the lowest mintage with only 600,000 coins struck.</p>

    <p>From 1856 to 1859, at both the Philadelphia and New Orleans mints Variety Two was resumed, meaning the coins no longer displayed arrows at the date, but the reduced weight of Variety Three remained as the standard weight. Mintages varied from a high of 7.3 million to a low of 340,000.</p>

    <p>(This is VARIETY FOUR – with “UNITED STATES OF AMERICA” surrounding Miss Liberty, removed from the reverse.)
Variety Four coins began in 1860 and ran until the denomination was discontinued in 1873. As one would expect with the outbreak of the Civil War in 1861, New Orleans coins bearing the “O” mintmark were struck in 1860 and that was the final year. Between 1861 and 1865, New Orleans was located deep in the heart of the Confederate States of America and New Orleans ceased to be a valuable branch mint for the United States. To pick up the slack and assist Philadelphia in coin production, coins were struck in San Francisco beginning in 1863 and running through the end of production in 1873.</p>

    <p>In 1978 a stunning event happened regarding these Liberty Seated Half Dimes. A collector purchased a coin in a dealer’s “junk box” that turned out to be a coin that did not exist. He located an 1870-S Liberty Seated Half Dime, of which the Mint had no records that the coin ever existed – yet there it was and it was examined and found to be genuine.</p>"
]
,
[
    "category_id" => 27,
    "description" => "<p>Barber dimes are among many coins that were designed by U.S. Mint Chief Engraver Charles E. Barber. These coins replaced the Liberty Seated dime design with a new, clean design that received mixed reception both at the time when it was circulating and today as it is collected.</p>

    <p>But these unique coins are a unique product of their time in American history and a testament to the legacy that Barber left on the world of numismatics. His influence is felt throughout this era of U.S. coinage, and the struggle between his utilitarian vision and the more aesthetic vision of other designers set the stage for some of the most iconic U.S. coin issues of all time.</p>

    <p>Barber Dime Design
    The Barber coins came about because of a failed contest.</p>

    <p>U.S. Mint Director Edward O. Leech took office in 1890 and one of his first acts was to propose a competition for a redesign of U.S. coinage. The competition was open to the public, though there was a short list of artists that were specifically asked to participate.</p>

    <p>The contest only offered a reward for the winner, and none of the well-known artists invited were enthusiastic about the idea. They came back with a counterproposal that Leech could not fulfill, and as a result, all the designs submitted came from the public and not the handpicked list of artists. Barber, Boston seal engraver Henry Mitchell and famous sculptor Augustus Saint-Gaudens were picked to judge the designs. Not one was accepted. Later in life, Barber claimed Saint-Gaudens objected to every design. The two men were rivals in coin design, with Barber’s simplistic and workmanlike designs stemming from his craft background, while Saint-Gaudens’s fine art training made him consider aesthetics more than practicality.</p>

    <p>With his plan to use the competition winner for a coin redesign failed, Leech asked Barber to prepare new designs for the Silver coinage currently in circulation (with the exception of the dollar, as the Morgan dollar was still being struck in large quantities).</p>

    <p>The decision to create the next set of designs in-house caused some controversy, as some who were involved with coin design were frustrated that a man who was not a fine artist was being given the responsibility for the design. The design took time to approve, with Leech and Barber going back and forth about the finer details. There are some early pattern coins available for 1891 with some of these rejected design elements.</p>

    <p>The obverse of the coin was the head of Liberty with a crown of olive leaves, and the reverse was a heraldic eagle on the larger coin sizes. For the time, a slight variant of the old reverse was used. The first coins of the new Barber design were struck in 1892, and the design (with slight modifications) ran through 1916.</p>

    <p>Historical Significance
    Barber’s were the first major redesigns for any American coinage in some time, and they marked a new era for U.S. coins after the English-inspired engravings that William Gobrecht had done for the previous Seated Liberty series.</p>

    <p>The problem that they ran into when they were trying to redesign coins in Barber’s day was that the U.S. Mint did not have the legal grounds to do so. This was changed by an act of Congress and signed into law by President Benjamin Harrison, creating the ability for the Mint to refresh the coin designs periodically with permission from the Secretary of the Treasury.</p>

    <p>These coins are not considered among the most beautiful American coins, but they are an important period in U.S. Mint history and coinage history as a whole.</p>

    <p>Numismatic Value
    Barber dimes are relatively common, and their major varieties were minted in fairly high numbers and have a good survival rate. Dimes in higher condition will fetch more, as will dimes with non-standard mint marks. The 1894-S dime is highly sought after and prohibitively expensive for all but the richest collectors, with only 24 known to exist. Each specimen is worth well over $1.5 million. The 1896-S and the 1901-S are both tough dates to find but can be found much more easily than an 1894-S.</p>

    <p>Shop our assortment of rare coins and currency and find your next Barber Dime today.</p>"
]
,
[
    "category_id" => 28,
    "description" => "<p>Bust Dimes (1796-1837)
    The dime has been struck with few interruptions from the beginning of U.S. coinage to the present day. These were sanctioned originally in the Coinage Act of 1792, and these small Silver coins have remained the 10-cent piece from their inception until now.</p>
    
    <p>Bust Dime Design
    The dime was originally called the “disme” (pronounced “deem”) in the Coinage Act, but once it was struck as a pattern coin the name rapidly changed to “dime” as Americans struggled to pronounce the French-origin word.</p>
    
    <p>The dime was struck for circulation first a few years after its smaller sibling the half dime and was based on the same metal composition. Bust dimes were struck from 89.24% Silver and 10.76% Copper, an alloy that was slightly modified for future releases.</p>
    
    <p>The Draped Bust design was created by Robert Scot, the chief engraver of the U.S. Mint at the time, and ran from 1796 to 1807. As part of a redesign of currently circulating U.S. coinage, Scot changed the reverse wreath to an olive wreath symbolizing peace. He also updated Liberty’s design to remove the cap and replace it with a ribbon as well as adding a drape-like garment to the bust (which gives this design its name). These ran with two different reverses and minor changes, including variable numbers of stars. There are also overstrikes that can be found in some years.</p>
    
    <p>The Capped Bust came next, a John Reich design engraved by William Kneass. After the dime began to be struck again in 1809, the portrait changed to a Liberty with a cap on her head. These coins ran through 1837 before being succeeded by the Liberty Seated design.</p>
    
    <p>Errors, overstrikes and varieties of these coins are more common in earlier years than they are in later years. That is because of the crude nature of the process by which they were made. Coin-making technology has come a long way since those strikes and later coins were changed less than these earlier varieties.</p>
    
    <p>Historical Significance
    Errors, overstrikes and varieties of these coins are more common in earlier years than they are in later years. That is because of the crude nature of the process by which they were made. Coin-making technology has come a long way since those strikes and later coins were changed less than these earlier varieties.</p>
    
    <p>Numismatic Value
    Early dimes are very hard to find, but later mintages are more common. Some early types in the Capped Bust series are available for a reasonable price in lower grades. Higher grades can get prohibitively expensive very fast. Draped Bust examples in higher grades are out of range of all but the most dedicated and well-heeled collectors, and even low-grade examples cost hundreds of dollars.</p>
    
    <p>The higher populations of later series like the Liberty Seated make them a much easier prospect for intermediate collectors. Early examples of U.S. coinage including the dime are not easy to get one’s hands-on.</p>"
]
,
[
    "category_id" => 29,
    "description" => "<p>Mercury Dimes (1916-1945)
    The dime has been struck with few interruptions from the beginning of U.S. coinage to the present day. These were sanctioned originally in the Coinage Act of 1792, and these small Silver coins were the 10-cent piece from their inception until now.</p>
    
    <p>Mercury Dime Design
    The dime was struck for circulation first a few years after its smaller sibling the half dime and was based on the same planchet. Early dimes were struck from 89.24% Silver and 10.76% Copper, an alloy that was slightly modified for future releases like the Liberty Seated. The alloy was changed at that point to 90% Silver, a percentage it would retain until the demise of most U.S. Silver coinage in 1964. The Mercury dime was minted out of this 90% Silver alloy.</p>
    
    <p>The coin is commonly called a “Mercury” dime as it resembled the Roman god, but just like many other U.S. coins, the head is a representation of Liberty. This head has wings as a representation of “liberty of thought.” Adolph Weinman’s design was well-liked, but it had some problems with vending machines and the design needed minor modifications after the fact.</p>
    
    <p>Weinman’s design overlapped with the Barber dime in 1916 due to the time it took for the design to be approved. The months of no new coins meant the U.S. Mint had to do something to address the problem of a possible coin shortage, and acting director Fred H. Chaffin struck Barber dimes and quarters to meet demand. When the Mercury dime was approved, the issues with striking put them even further behind, and for much of the year, the Barber coins were the only coins available. Mercury dimes were struck starting on October 30, 1916.</p>
    
    <p>Weinman had studied under famed sculptor Augustus Saint-Gaudens, well-known for his design of the double eagle, and they shared some similar tastes which can be seen in the Mercury dime. The feathers in relief are a Saint-Gaudens touch shared by many of his apprentices.</p>
    
    <p>The obverse is Liberty with a winged cap, while the reverse is a fasces with olive branches. The fasces combined with the olive branches symbolizes unity as well as peace, and the fasces may also represent war and justice. This ancient Roman symbol of authority is in keeping with the coin’s style.</p>
    
    <p>The Mercury Dime ran until 1945 when President Franklin D. Roosevelt died. The public clamored for a new coin with his face, and the close association Roosevelt had with the March of Dimes made the 10-cent coin an obvious choice. The Mercury dime was thus replaced by the Roosevelt.</p>
    
    <p>Historical Significance
    The Mercury dime was part of one of the most dynamic eras in U.S. circulating coinage, a time when many new ideas were being brought to the table and put into action. Theodore Roosevelt’s push to modernize U.S. coins wrought far-reaching changes even after he left office, and though the Mercury dime may not owe its genesis to him the movement he pushed laid the foundations for today’s coins.</p>
    
    <p>Numismatic Value
    Mercury dimes are readily available for most years in lower grades, but there are a few key dates and errors that are highly collectible. The 1916-D had a very low mintage due to the Denver mint switching to mostly quarters to keep up with the production of those, meaning dimes were barely minted. The 1921 and 1921-D coins also have low mintages and are favorites among collectors. Lastly, the 1942/1 overdate is extremely popular, and thus somewhat pricey even in lower grades because the overdate is clearly visible to the naked eye.</p>
    
    <p>Shop our assortment of rare coins and add Mercury Dimes to your numismatic collection today.</p>"
]
,
[
    "category_id" => 30,
    "description" => "<p>Roosevelt Dimes (1946-)
    The dime has been struck with few interruptions from the beginning of U.S. coinage to the present day. These were sanctioned originally in the Coinage Act of 1792, and these small Silver coins were the 10-cent piece from their inception until now. The Roosevelt dime bridged the gap between the Silver coins of previous generations and the new cupro-nickel coins of today.</p>
    
    <p>Roosevelt Dime Design
    The Roosevelt dime was preceded by the so-called “Mercury” dime, named for the winged head of Liberty on the obverse. The Mercury Dime ran until 1945 when President Franklin D. Roosevelt died. The public clamored for a new coin with his face, and the close association Roosevelt had with the March of Dimes made the 10-cent coin an obvious choice. The Mercury dime was thus replaced by the Roosevelt. This process took place rapidly. The new dime was designed and in production by 1946.</p>
    
    <p>The dime has been in production with mostly the same design since that year. Minor changes have been made (slight changes to the portrait’s hair and the movement of the mint mark), but other than that this coin has existed with little change.</p>
    
    <p>The design of this coin, created by chief engraver John Sinnock has an obverse with Roosevelt’s face and a reverse with a torch flanked by an olive and oak sprig. This reverse is similar to the Mercury dime, but removes the fasces for a more recognizable image and adds the oak.</p>
    
    <p>The one major change that occurred with this coin is the one that happened to all U.S. Silver coinage. The planchets for dimes used to be made from 90% Silver, but the 1965 Coinage Act changed all that. Starting in the late 1950s, people had begun hoarding U.S. Silver coinage due to increased demand, which drove the price of Silver well over the face value of the coins. This led to chronic shortages, and when it became clear that the Mint’s Silver stocks would not hold up to this demand the government decided to step in.</p>
    
    <p>At this point, U.S. Silver coinage for all small denominations was changed to cupro-nickel, with dimes being made from a cupro-nickel alloy over a copper core. Half dollars were debased and reduced to just 40% Silver until 1970 when halves were made entirely of cupro-nickel.</p>
    
    <p>Historical Significance
    Franklin Delano Roosevelt’s death was an important event in American history given how long he was in office and the fact that he was president through the Great Depression and World War II. The establishment of his face as the face of a coin immediately after his death indicated the impact he made. The dime bearing his name was struck first on January 19, 1946, and went into circulation on January 30 — which would have been his birthday.</p>
    
    <p>Numismatic Value
    Roosevelt dimes are still collected today, but many are collected primarily for their bullion content as “90%” or “Junk” Silver. The same goes for quarters from the same time period. Low-grade specimens are bought in bulk bags and sold in lots instead of for their numismatic value. Most collectible dimes are available for a very reasonable price (barring errors) due to the very high mintages and survival rates. There are no particularly rare dates.</p>
    
    <p>Expand your collection today and shop our assortment of Roosevelt Dimes (1946-Date).</p>"
]
,
[
    "category_id" => 31,
    "description" => "<p>Learn About 52 Varieties of 1837-1891 Liberty Seated Dimes
    Although Capped Bust Dimes were minted first in 1837, there was an impetus to change the design again. As with the Half Dime coinage, the task fell to Christian Gobrecht who was the Second Engraver of the United States Mint. He had worked for the US Mint as early as 1823, but it was a temporary position. Gobrecht finally became the Chief Engraver in 1840 and held the position until his death in 1844.</p>

    <p>The design for the new dime was a duplicate of his new Half Dime and was similar to his pattern silver dollars of 1835 and 1836 (the Gobrecht Dollars) and was simple in its presentation. The obverse of the coin had Liberty, seated, facing left, holding a shield in one hand and a pole with a cap on the end of it in the other.  The only distraction to that symbolism was the date below Miss Liberty. The reverse had the denomination “ONE DIME” in two lines, enclosed inside a wreath with “UNITED STATES OF AMERICA” around it.</p>

    <p>(This is the VARIETY ONE – No Stars on Obverse – minted in 1837 and 1838.) The 1837 coin was minted in Philadelphia and 682,500 were struck. There were two varieties – a Small Date and a Large Date, neither of them is particularly more valuable than the other. The only other coin issued as a Variety One was an 1838-O struck in New Orleans, with 489,034 coins actually minted. It is a bit scarcer than either of the 1837 varieties.</p>

    <p>(This is VARIETY TWO – in 1838 13 stars were added to the obverse on all Philadelphia coins.) In 1838 the Philadelphia Mint added 13 stars above and around Miss Liberty and this was continued through 1859. The New Orleans “O” mintmark appears above the bow on the reverse. In 1840, a fold of drapery was added from the right elbow on Miss Liberty. Coins were struck in Philadelphia and New Orleans and the design was unchanged until 1853.</p>

    <p>1838 dated coins come in Large and Small stars varieties and a partial drapery variety. There were 1,992,500 1838-dated coins minted in Philadelphia. 1839 saw over 1 million coins struck both at Philadelphia and New Orleans and they are valued similarly.  Philadelphia struck under 1 million coins in 1840 and New Orleans struck under 1.1 million coins with an “O” mintmark that same year. The same pattern was repeated in 1841 with Philadelphia over 1.6 million coins and New Orleans just over 2 million. In 1842 the main mint struck 1.9 million and the branch mint just over 2 million coins.</p>

    <p>New Orleans struck only 150,000 coins in 1843, while Philadelphia struck nearly 1.4 million. In 1844 mintages dropped at both mints with Philadelphia striking 72,500 coins and New Orleans not striking any coins. In 1845, the Philadelphia Mint struck 1.7 million coins and the New Orleans branch struck only 230,000 “O” dimes. Between 1846 and 1848, coins were only struck in Philadelphia. 1846 was a very difficult year with only 31,300 minted making any dime dated 1846 very scarce in any grade. 1849 saw 839,000 coins struck and in New Orleans exactly 300,000 were struck that same year. In both 1850 and 1851 coins were struck at both mints and the mintages ranged from just under two million to a low of 400,000.</p>

    <p>In 1852 over 1.5 million coins were struck in Philadelphia but only 430,000 in New Orleans. The 1853 Philadelphia coin has the lowest mintage with only 95,000 coins struck.  Again, a new variety was about to be struck.</p>

    <p>(This is VARIETY THREE – arrows added at the date on the obverse to signal a reduction in the silver weight.) Later in the year 1853, arrows were placed on each side of the date to denote a reduction in the Silver weight of the coins. This continued from 1853 through 1855 and applied to coins minted both in Philadelphia and New Orleans, though no New Orleans coins were struck in 1855. 1853 from Philadelphia had the highest mintage at nearly 12.2 million coins while the 1853-O had the lowest mintage with 1.1 million coins struck.</p>

    <p>From 1856 to 1860, at the Philadelphia, New Orleans and at the San Francisco mint in 1859 and 1860, the Variety Two coin was resumed, meaning the coins no longer displayed arrows at the date, but the reduced weight of Variety Three remained as the standard weight. Mintages varied from a high of 5.8 million to a low of only 60,000.</p>

    <p>(This is VARIETY FOUR – with “UNITED STATES OF AMERICA” surrounding Miss Liberty, removed from the reverse.) Variety Four coins were now redesigned by James B. Longacre and this style began in 1860 and ran until 1873. As one would expect with the outbreak of the Civil War in 1861, New Orleans coins bearing the “O” mintmark were struck in 1860 and that was the final year, with only 40,000 coins struck at our branch mint. Between 1861 and 1865, New Orleans was located deep in the heart of the Confederate States of America and New Orleans ceased to be a valuable branch mint for the United States. To pick up the slack and assist Philadelphia in coin production, coins were struck in San Francisco beginning in 1861 and running through 1872. 1871 to 1873 also had coins struck at the branch mint in Carson City, Nevada.</p>

    <p>(This is VARIETY FIVE – with Arrows added at the date to indicate an increase in the weight.) Variety Five coinage was continued for only two years – 1873 and 1874 at Philadelphia, San Francisco and Carson City.  The 1873 and 1874 Carson City coins are rarities with 18,791 and 10,817 coins respectively for those two years.</p>

    <p>Beginning in 1875 and running until the series ended in 1891, the Variety Four coins were once again resumed. There are even rarities in this last run of the series. 1879, 1880 and 1881 Philadelphia coins are all extremely low mintage rarities with 14,000, 36,000 and 24,000 coins respectively in those years. A later date – 1885-S – is also a rarity with 43,690 coins struck.</p>"
]
,
[
    "category_id" => 32,
    "description" => ""
],
[
    "category_id" => 33,
    "description" => "<p>Standing Liberty Quarters (1916-1930)
    The quarter has been struck with few interruptions from the beginning of U.S. coinage to the present day. These were sanctioned originally in the Coinage Act of 1792, and the quarter has been one of the foundational coins of the U.S. monetary system from that point to the present day. They were first minted in 1796. The Standing Liberty type ran from 1916 to 1930, when it was replaced by Washington.</p>

    <p>Standing Liberty Quarter Design
    The Standing Liberty came after the Barber, and it ran from 1916 to 1930. These coins were created as a replacement for the Barber designs because the director of the U.S. Mint, Robert W. Woolley, thought that the 25-year minimum for a new coin design was actually a mandate to create a new coin when the time period was up.</p>

    <p>Woolley and the Commission of Fine Arts worked together on the Silver coins including the dime, quarter and half dollar. Many were agitating for the replacement of the Barber coins as part of an ongoing refresh to make the coins more beautiful. Not all were pleased by this. Chief Engraver Barber, long an advocate for practicality over aesthetics, provided some roadblocks to the process though he eventually did put the designs through.</p>

    <p>The original suggestion had been for the Mint to create new designs, and Barber had done so. The commission did not appreciate the sketches Barber submitted and recruited sculptors to create designs. Adolph Weinman, Hermon MacNeil and Albin Polasek were the three selected. Weinman’s designs were used in their entirety for the dime and the half dollar. MacNeil designed the quarter, and none of Polasek’s designs were selected.</p>

    <p>The new coins were publicly announced on March 3, 1916. With these design refreshes, all American coins would have a new design. This was also the first time that there was a clearly different design between all different coins, as many of the older designs were used for multiple coins with minor modifications.</p>

    <p>The Standing Liberty design shows Liberty in something of a militaristic posture, holding a shield in one hand and an olive branch in the other as she walks through a gate in a wall. This obverse came from Adolph Weinman. The reverse depicts an eagle in flight, similar to the Gobrecht dollar obverse. This design went through a few revisions before its creation and pattern coins exist with a variety of changes, but when MacNeil saw the changes the Mint had made without his input he was highly critical and the new quarters that were struck did not leave the mint. Liberty’s bare breast on the 1916 and some 1917 quarters was covered in the latter runs during 1917. These 1917 quarters are popular among collectors as they will collect both Type 1 (bare breast) and Type II (covered breast). The only other significant change came in 1925 when modifications were made to make the date last longer — and subsequent issues are more likely to have an intact date because of this change which lowered the relief.</p>

    <p>Historical Significance
    This coin had a short run as circulating coins go, but it coincided with the Great Depression, and it is missing a year because of the Great Depression causing such a lull in commerce. 1931 quarters do not exist.</p>

    <p>Numismatic Value
    Condition for these plays a key role in their value, as many were well circulated. In general the older the coin is, the more valuable it is. This is usually not the case for any series. Branch mint versions are also more valuable than regular issues, particularly in high grades and early issues. 1916 is a key date, and high-grade specimens are hard to find due to the problems with dates wearing off.</p>

    <p>Expand your collection today and shop our assortment of Standing Liberty Quarters (1916-1930).</p>"
]
,
[
    "category_id" => 34,
    "description" => "<p>Learn About Capped Bust Quarters and John Reich’s Design
    In 1800, John Reich came to America and applied for a job at the U.S. Mint. After being recommended for employment by President Jefferson in 1801, he was hired. For several years, he did small re-engravings or other minor work. In 1807 he was given the task of creating completely new designs. He designed the “Capped Bust” coinage, first appearing on Half Dollars in 1807 and later on the Dime in 1809. The design didn’t make its way to the Quarter Dollar until 1815, as no quarters were struck between 1808 and 1814.</p>
 
    <p>Early quarters fell victim to Gresham’s Law. This is a monetary principle maintaining that “bad money drives out good.” It is mainly used for consideration and application in currency markets. Gresham’s Law was originally based on the composition of minted coins and the value of the Precious Metals used in them.</p>

    <p>Capped Bust Quarters Design
    Reich’s design portrays Miss Liberty facing left, wearing a Phrygian cap with LIBERTY emblazoned across the headband. Below Miss Liberty is the date, surrounded by 13 stars – 7 to the left and 6 to the right. The reverse features a version of the Heraldic Eagle perched on a branch while holding three arrows, a shield on its breast and E PLURIBUS UNUM on a scroll above. The denomination “25 C.” is at the bottom of the coin.</p>

    <p>In the first year of issue, 1815, 89,235 coins were struck. A fire at the mint in 1816 destroyed most of the equipment, shutting down the production of quarters until 1818.</p>

    <p>1818: 361,174 mintage, with two varieties, a normal date and a 1818/5 overdate.</p>

    <p>1819: 144,000 mintage, with two varieties, a small 9 and a large 9 in the date.</p>

    <p>1820: 127,444 mintage, with two varieties, a large 0 and a small 0.</p>

    <p>1821: 216,851 mintage.</p>

    <p>1822: 64,080 mintage, with two varieties, the normal date and the 1825 25 C. over 50 C.</p>

    <p>The 1823/2 Capped Bust Quarter only had one major variety, which happened to be an overdate error. All quarters of this year had an 1823 date struck over the 1822 date. This is an extremely rare and valuable year, with a limited mintage of 17,800.</p>

    <p>1824: 168,000 mintage. All 1824-dated coins are 1824/2 overdates.</p>

    <p>1825: The mintages of both the 1825 5/2 and 1825 5/4 coins are unknown.</p>

    <p>Capped Bust Quarters Varieties
    There are certain Large Size Capped Bust quarters shrouded with mystery. Mostly dated 1815 and 1825, some coins have a large “E” or “L” counter stamped above Liberty’s head. These marks are unmentioned in official records and their purpose unknown. Though nothing has been confirmed, it is speculated that they could have been made to use as school prizes.</p>

    <p>1826: No 1826-dated coins struck were struck.</p>

    <p>1827: 4,000 mintage.</p>

    <p>1827 Restrike: A much greater number of coins were struck of the 1827 restrike coins. The originals and restrikes can be identified by the style of the 2 on the reverse of the denomination. 1827 originals have a curl base 2 since they were struck using a reverse die of 1828. The restrikes have a square base 2, as they were struck with a reverse die of 1819.</p>

    <p>1828: 102,000 mintage. Among the 1828-dated coins, there was a scarcer version with 25C over 50C in the denomination.</p>

    <p>Capped Bust Quarters Mintage
    Coins struck between 1815 to 1828 are called Large Size Capped Bust Quarters, because they measured 27 milliliters in diameter. In 1829, construction began on the new Philadelphia Mint building. Engravers and designers had more space and time for renovations to the coin designs. During this time, William Kneass modified the Capped Bust Quarter design. The redesigned Reduced Diameter Capped Bust Quarter was smaller, measuring only 24.3 millimeters in diameter. Kneass kept Reich’s basic design, but he altered Miss Liberty’s appearance, making her appear more youthful. He also removed the banner with E PLURIBUS UNUM above the eagle on the reverse. This design was used from 1831 to 1838.</p>

    <p>In 1829, several mechanical improvements were made in the striking of half dimes, but it would not be until 1831 that these same procedures could be used to produce quarters. The new procedure, known as the “close collar” implemented what Mint Director Samuel Moore called a “mathematical equality” to the quarters. A higher, beaded border was also incorporated around the rims, which served to protect the interior surface of the coin.</p>

    <p>1831: 398,000 mintage, with two varieties, featuring large and small letters.</p>

    <p>1832: 320,000 mintage. All known 1832-dated coins were struck from a single new obverse die coupled with two reverse dies leftover from 1831.</p>

    <p>1833: 156,000 mintage, with two varieties, featuring the 1833 coin as normal and the other depicting an “o” over “f” overstrike.</p>

    <p>Shop our assortment of Capped Bust Quarters and expand your collection today.</p>"
]
,
[
    "category_id" => 35,
    "description" => "<p>Bust Quarters (1796-1838)
    The quarter has been struck with few interruptions from the beginning of U.S. coinage to the present day. These were sanctioned originally in the Coinage Act of 1792, and the quarter has been one of the foundational coins of the U.S. monetary system ever since. They were first minted in 1796 and can still be found in pockets and purses today.</p>
 
    <p>Bust Quarter Design
    Quarters are a quarter of a dollar and were one of the several denominations authorized by the Coinage Act of 1792. Early quarters, as with all other U.S. Silver coinage of the time, used an 89.24% Silver and 10.76% Copper composition, an alloy that was slightly modified in future releases.</p>
 
    <p>The Draped Bust design was created by Robert Scot, the chief engraver of the U.S. Mint at the time, and ran from 1796 to 1807. As part of a redesign of currently circulating U.S. coinage, Scot changed the reverse wreath to an olive wreath symbolizing peace while keeping the eagle. He also updated Liberty’s design to remove the cap and replace it with a ribbon as well as adding a drape-like garment to the bust (which gives this design its name). These ran with two different reverses and minor changes, including variable numbers of stars. There are also overstrikes that can be found in some years.</p>
 
    <p>The Capped Bust came next, a John Reich design engraved by William Kneass. After the quarter began to be struck again in 1815, the portrait changed to a Liberty with a cap on her head. This type ran through 1838 before being succeeded by the Liberty Seated design.</p>
 
    <p>Errors, overstrikes and varieties of these coins are more common in the earlier years than they are in later coinage. That is because of the crude nature of the process by which they were made. Coin-making technology has come a long way since those strikes and later coins were changed less than these earlier varieties.</p>
 
    <p>Historical Significance
    Early U.S. coins were not the only coins post-colonial America had. French, Spanish and English coins circulated freely and were accepted as legal tender, and the mintage of new U.S. money did not stop these from circulating. The quarter circulated but competed against these coins, particularly these early Bust quarters. These were important because they were among the first U.S. coins minted, but in terms of day-to-day living at the time of their mintage, their impact was muted. It was not till far later that foreign coinage was banned from use as legal tender and U.S. coins became the standard.</p>
 
    <p>Numismatic Value
    Early quarters are very hard to find, but later years are more common. Some early specimens in the Capped Bust series are available for a reasonable price in lower grades. Higher grades can get prohibitively expensive very fast. Draped Bust examples in higher grades are out of range of all but the most dedicated and well-heeled collectors, and even low-grade examples cost hundreds of dollars. Some Capped Bust overstrikes are extremely hard to find and command a massive premium at auction.</p>
 
    <p>The higher mintages of later series like the Liberty Seated make them a much easier prospect for intermediate collectors. Early examples of U.S. coinage including the quarter are not easy to get one’s hands-on.</p>
 
    <p>Expand your collection today and shop our assortment of Bust Quarters (1796-1838).</p>"
]
,
[
    "category_id" => 36,
    "description" => "<p>Seated Liberty Quarters (1838-1891)
    The quarter has been struck with few interruptions from the beginning of U.S. coinage to the present day. These were sanctioned originally in the Coinage Act of 1792, and the quarter has been one of the foundational coins of the U.S. monetary system ever since. They were first minted in 1796 and can still be found in pockets and purses today. Specifically, the Liberty Seated Quarter ran from 1838 to 1891, when it was replaced by the Barber.</p>
 
    <p>Seated Liberty Quarter Design
    Early quarters were struck from 89.24% Silver and 10.76% Copper, an alloy that was slightly modified at the time of the Liberty Seated release. The alloy was changed at that point to 90% Silver, a percentage it would retain until the demise of most U.S. Silver coinage in 1964.</p>
 
    <p>The Liberty Seated series had a representation of Liberty sitting on a rock, holding a Liberty pole with a Phrygian cap on the top (this symbol of freedom can be seen prominently in Capped Bust coins). The reverse on dimes had a wreath of laurel leaves, while larger coins such as the quarter, half dollar and dollar had an eagle.</p>
 
    <p>This coin saw a number of small variations over the course of its run, including different number sizes, changes to the drapery around Liberty, the number and size of stars and more. One variety of note is the arrow by the date, which indicated a slight change in the size of the coin to bring the intrinsic value of the metal more in line with the face value. This change occurred from 1853 to 1855 and from 1873 to 1874. After a couple of years, they were changed back to the variant they had been before the arrows were implemented, as this was only a temporary measure to get people used to the changed coinage.</p>
 
    <p>The Liberty Seated quarter survived through the Civil War, though many hoarded coins during this time period. These coins stayed in production till 1891, but changes in public approval and a push towards more interesting coinage led to the redesign of all varieties that could be touched, including the quarter. This led to the Barber series.</p>
 
    <p>Historical Significance
    Early U.S. coins were not the only coins post-colonial America had. French, Spanish and English coins circulated freely and were accepted as legal tender, and the mintage of new U.S. money did not stop these from circulating. The quarter circulated but competed against these coins, particularly these early Bust quarters. These were important because they were among the first U.S. coins minted, but in terms of day-to-day living at the time of their mintage, their impact was muted.</p>
 
    <p>The Coinage Act of 1857 occurred while the Liberty Seated was being minted. Businesses that had previously accepted any form of money as legal tender now would only accept U.S. coins, and that drove demand far exceeding that of previous years. That set the stage for our current monetary system.</p>
 
    <p>Numismatic Value
    Liberty Seated quarters are available for a reasonable price for many years in low grades, but the price rises rapidly for higher grades. There are some very expensive, low mintage and rare examples that make this a very difficult but exciting and rewarding series to collect.</p>
 
    <p>The higher mintages later in the series as opposed to earlier quarters make the Liberty Seated quarter series a set for intermediate collectors to get into. Early examples of U.S. coinage including the quarter are not easy to get one’s hands-on.</p>
 
    <p>Expand your collection today and shop our assortment of rare coins and currency Liberty Seated Quarters (1838-1891).</p>"
]
,
[
    "category_id" => 37,
    "description" => "<p>
    The quarter has been struck with few interruptions from the beginning of U.S. coinage to the present day. These were sanctioned originally in the Coinage Act of 1792, and the quarter has been one of the foundational coins of the U.S. monetary system from that point to the present day. They were first minted in 1796. The Standing Liberty type ran from 1916 to 1930, when it was replaced by Washington.</p>
 
    <p>Standing Liberty Quarter Design
    The Standing Liberty came after the Barber, and it ran from 1916 to 1930. These coins were created as a replacement for the Barber designs because the director of the U.S. Mint, Robert W. Woolley, thought that the 25-year minimum for a new coin design was actually a mandate to create a new coin when the time period was up.</p>
 
    <p>Woolley and the Commission of Fine Arts worked together on the Silver coins including the dime, quarter and half dollar. Many were agitating for the replacement of the Barber coins as part of an ongoing refresh to make the coins more beautiful. Not all were pleased by this. Chief Engraver Barber, long an advocate for practicality over aesthetics, provided some roadblocks to the process though he eventually did put the designs through.</p>
 
    <p>The original suggestion had been for the Mint to create new designs, and Barber had done so. The commission did not appreciate the sketches Barber submitted and recruited sculptors to create designs. Adolph Weinman, Hermon MacNeil and Albin Polasek were the three selected. Weinman’s designs were used in their entirety for the dime and the half dollar. MacNeil designed the quarter, and none of Polasek’s designs were selected.</p>
 
    <p>The new coins were publicly announced on March 3, 1916. With these design refreshes, all American coins would have a new design. This was also the first time that there was a clearly different design between all different coins, as many of the older designs were used for multiple coins with minor modifications.</p>
 
    <p>The Standing Liberty design shows Liberty in something of a militaristic posture, holding a shield in one hand and an olive branch in the other as she walks through a gate in a wall. This obverse came from Adolph Weinman. The reverse depicts an eagle in flight, similar to the Gobrecht dollar obverse. This design went through a few revisions before its creation and pattern coins exist with a variety of changes, but when MacNeil saw the changes the Mint had made without his input he was highly critical and the new quarters that were struck did not leave the mint. Liberty’s bare breast on the 1916 and some 1917 quarters was covered in the latter runs during 1917. These 1917 quarters are popular among collectors as they will collect both Type 1 (bare breast) and Type II (covered breast). The only other significant change came in 1925 when modifications were made to make the date last longer — and subsequent issues are more likely to have an intact date because of this change which lowered the relief.</p>
 
    <p>Historical Significance
    This coin had a short run as circulating coins go, but it coincided with the Great Depression, and it is missing a year because of the Great Depression causing such a lull in commerce. 1931 quarters do not exist.</p>
 
    <p>Numismatic Value
    Condition for these plays a key role in their value, as many were well circulated. In general the older the coin is, the more valuable it is. This is usually not the case for any series. Branch mint versions are also more valuable than regular issues, particularly in high grades and early issues. 1916 is a key date, and high-grade specimens are hard to find due to the problems with dates wearing off.</p>
 
    <p>Expand your collection today and shop our assortment of Standing Liberty Quarters (1916-1930).</p>"
]
,
[
    "category_id" => 38,
    "description" => "<p>The quarter has been struck with few interruptions from the beginning of U.S. coinage to the present day. These were sanctioned originally in the Coinage Act of 1792, and the quarter has been one of the foundational coins of the U.S. monetary system from that point to the present day. They were first minted in 1796. The Washington type has run from 1932 to the present day, with current quarters featuring rotating reverses.</p>
 
    <p>Washington Quarter Design
    The original suggestion had been for the Mint to create new designs, and Barber had done so. The commission did not appreciate the sketches Barber submitted and recruited sculptors to create designs. They chose the half dollar as their target.</p>
 
    <p>Then-president Herbert Hoover disliked commemorative coins like the half dollars that had been common issues for the time period, and he expressed his concerns in his veto. Hoover pointed to counterfeit concerns and the number of commemoratives that went unsold.</p>
 
    <p>The Bicentennial Committee and the Fine Arts Commission agreed to a half dollar with Washington’s face based on the bust created by Jean-Antoine Houdon, and a design was chosen through a contest. The winner, Laura Gardin Fraser, was the wife of the Buffalo nickel designer James Earle Fraser and a coin designer herself who created several commemorative coins.</p>
 
    <p>Unfortunately for her, legislation was introduced in the House of Representatives for a Washington quarter, and it was quickly passed. Fraser’s design may have been accepted by the Bicentennial Committee and Fine Arts Commission, but the Treasury had not been consulted. They conducted their own contest for the quarter. Sculptor John Flanagan won the contest, and his obverse and reverse were used.</p>
 
    <p>The obverse of the Washington quarter is a bust of George Washington based on a sculpture by Jean-Antoine Houdon. It was created after Houdon took a life mask of Washington, then used that to create a marble sculpture that now sits in the Virginia State Capitol. Both Flanagan and Fraser’s designs created their Washington rendition based on Houdon’s template.</p>
 
    <p>The reverse is a heraldic eagle with its wings spread. This design lasted through 1998, and new state reverse designs were created in 1999. There was one break from 1975 to 1976 when the Bicentennial quarter was issued with a separate reverse commemorating the nation’s founding.</p>
 
    <p>In 1999, the 50 State Quarters Program created new reverses with five states each year getting a unique reverse. In 2009 the District of Columbia and U.S. territories got their own reverses, then in 2010, a new program called the America the Beautiful Quarters Program was authorized to run through 2021. These reverse honor areas of natural or historic significance in the U.S.</p>
 
    <p>The obverse design has stayed mostly the same with minor changes over the decades, though the reverse has changed several times. This obverse is one of the longest-lasting in American coinage, second only to the Lincoln cent.</p>
 
    <p>The original composition of these coins was Silver, but as people began to hoard Silver coins through the early 1960s, the U.S. government began to consider other forms of coinage. In 1964 the quarter and dime were changed from 90% Silver to a cupro-nickel alloy. Half dollars were changed to 40% Silver.</p>
 
    <p>Historical Significance
    Originally the Washington Quarter was meant to be a one-year commemorative issue, included in the classic commemorative series. However, due to popular demand and the struggle of striking the Standing Liberty Quarter, the Washington Quarter remained a regularly circulating coin. In 1964 the Washington quarter was part of the change from Silver coinage to base metal coinage and as such is a big part of numismatic history in the U.S.</p>
 
    <p>Numismatic Value
    Many of these quarters that were struck before 1964 are collected primarily for their Silver value and sold in bulk rolls or bags, but some are rare and highly collectible. In particular, the 1932-D and 1932-S quarters are highly prized, particularly in higher grades. The majority of post-1964 Washington quarters are common and inexpensive, even in higher grades.</p>
 
    <p>Expand your collection today and shop our assortment of Washington Quarters.</p>"
]
,
[
    "category_id" => 39,
    "description" => "<p>Barber half dollars are among many coins that were designed by U.S. Mint Chief Engraver Charles E. Barber. These coins replaced older designs with a new, clean design that received mixed reception both at the time when it was circulating and today as it is collected.</p>
 
    <p>But these unique coins are a product of their time in American history and a testament to the legacy that Barber left on the world of numismatics. His influence is felt throughout this era of U.S. coinage, and the struggle between his utilitarian vision and the more aesthetic vision of other designers set the stage for some of the most iconic U.S. coin issues of all time.</p>
 
    <p>Barber Half Dollar Design
    The Barber coins came about because of a failed contest.</p>
 
    <p>U.S. Mint Director Edward O. Leech took office in 1890 and one of his first acts was to propose a competition for a redesign of U.S. coinage. The competition was open to the public, though there was a short list of artists that were specifically asked to participate.</p>
 
    <p>The contest only offered a reward for the winner, and none of the well-known artists invited were enthusiastic about the idea. They came back with a counterproposal that Leech could not fulfill, and as a result, all the designs submitted came from the public and not the handpicked list of artists. Barber, Boston seal engraver Henry Mitchell and famous sculptor Augustus Saint-Gaudens were picked to judge the designs. Not one was accepted. Later in life, Barber claimed Saint-Gaudens objected to every design. The two men were rivals in coin design, with Barber’s simplistic and workmanlike designs stemming from his craft background, while Saint-Gaudens’s fine art training made him consider aesthetics more than practicality.</p>
 
    <p>With his plan to use the competition winner for a coin redesign failed, Leech asked Barber to prepare new designs for the Silver coinage currently in circulation (with the exception of the dollar, as the Morgan dollar was still being struck in large quantities).</p>
 
    <p>The decision to create the next set of designs in-house caused some controversy, as some who were involved with coin design were frustrated that a man who was not a fine artist was being given the responsibility for the design. The design took time to approve, with Leech and Barber going back and forth about the finer details. There are some early pattern coins available for 1891 with some of these rejected design elements.</p>
 
    <p>The obverse of the coin was the head of Liberty with a crown of olive leaves, and the reverse was a heraldic eagle on the larger coin sizes. For the time, a slight variant of the old reverse was used. The first coins of the new Barber design were struck in 1892, and the design (with slight modifications) ran through 1916.</p>
 
    <p>Historical Significance
    Barbers were the first major redesigns for any American coinage in some time, and they marked a new era for U.S. coins after the English-inspired engravings that William Gobrecht had done for the previous Seated Liberty series.</p>
 
    <p>The problem that people ran into when they were trying to redesign coins in Barber’s day was that the U.S. Mint did not have the legal grounds to do so. This was changed by an act of Congress and signed into law by President Benjamin Harrison, creating the ability for the Mint to refresh the coin designs periodically with permission from the Secretary of the Treasury.</p>
 
    <p>These coins are not considered among the most beautiful American coins, but they are an important period in U.S. Mint history and coinage history as a whole.</p>
 
    <p>Numismatic Value
    The Barber half-dollar design was revised slightly through the life of the coin as Barber made practical adjustments. As a result, there are many clear features from year to year that can show when a Barber quarter was made. High mintages mean that these coins are fairly common and inexpensive in lower grades, though high grades are worth more.</p>
 
    <p>Like other Barber coins, the San Francisco and New Orleans mint mark is worth more due to the lower mintages and surviving populations. The 1892-O micro O variation is one that will fetch a high premium, particularly if in uncirculated condition.</p>
 
    <p>Expand your collection today and shop our assortment of Barber Half Dollars (1892-1915).</p>"
]
,
[
    "category_id" => 40,
    "description" => ""
]
,
[
    "category_id" => 41,
    "description" => "<p>The Redesign of the Draped Bust Half Dollar
    No Draped Bust Half Dollars were struck between the last year of the small eagle design, 1797 and 1800. In 1801, another redesign effort took place. Robert Scot was asked to redesign this coin again, for the third time in less than ten years.</p>
 
    <p>The reverse of the coin underwent a complete overhaul. The small eagle variety was ridiculed as a scrawny eagle, and that was replaced with a more regal heraldic eagle design. The eagle, itself, was much larger and had outstretched wings. On the chest was a union shield, with 13 six-pointed stars above and a grouping of clouds above the stars. In the eagle’s beak was a banner upon which the phrase “E PLURIBUS UNUM” was inscribed. In the left talon was an olive branch, and in the right were 13 arrows. The motto “UNITED STATES OF AMERICA” encircled the design. The edge (rim) of the coin bore the words “FIFTY CENTS OR HALF A DOLLAR.”</p>
 
    <p>Draped Bust Half Dollars Mintage
    1801: 30,289 mintage.</p>
 
    <p>1802: 29,890 mintage.</p>
 
    <p>1803: 188,324 mintage. Two varieties were struck, one with a small 3 and another with a large 3.</p>
 
    <p>1804: No coins were struck.</p>
 
    <p>1805 saw two varieties struck in the 211,722 coins minted. The 1805 date and a 1805/4 overstrike, with the second variety being nearly twice as scarce. Then in 1806, because the mintage soared to 839,576 coins minted, there were eight different die varieties. Six of the varieties are common while one is a bit scarce while the other one, the 1806 Knobbed 6, is a major rarity. Finally, in 1807 there were 301,076 coins minted, all of the same variety.</p>
 
    <p>Browse our selection of rare coins and add a Draped Bust Half Dollar to your collection today.</p>"
]
,
[
    "category_id" => 42,
    "description" => "<p>The half dollar was one of the foundational denominations of American coinage from the very beginning, and though it has fallen out of favor today it played a huge role in the monetary system of previous years. These coins were sanctioned originally in the Coinage Act of 1792 and were first minted in 1794.</p>
 
    <p>Flowing Hair and Bust Half Dollar Design</p>
 
    <p>Half dollars were one of the several denominations authorized by the Coinage Act of 1792. Early half dollars, as with all other U.S. Silver coinage of the time, used an 89.24% Silver and 10.76% Copper composition, an alloy that was slightly modified in future releases.</p>
 
    <p>The first half dollars were struck in 1794. This design, called the Flowing Hair and engraved by Robert Scot, was shared with the half dime and dollar coins of the same time period and ran from 1794 to 1795. Few of these survive, and the ones that did are rare and expensive in higher grades.</p>
 
    <p>The Draped Bust design was created by Robert Scot, the chief engraver of the U.S. Mint at the time, and ran from 1796 to 1807. As part of a redesign of currently circulating U.S. coinage, Scot changed the reverse wreath to an olive wreath symbolizing peace while keeping the eagle. He also updated Liberty’s design to add a ribbon as well as adding a drape-like garment to the bust (which gives this design its name). These ran with two different reverses and minor changes, including variable numbers of stars. There are also overstrikes that can be found in some years.</p>
 
    <p>The Capped Bust came next, a John Reich design engraved by William Kneass. The half dollar changed the portrait to a Liberty with a cap on her head. These coins ran through 1839 before being succeeded by the Liberty Seated design. During the Capped Bust’s run, the edges of the half dollar were stamped with the words “FIFTY CENTS OR HALF A DOLLAR” until the year 1836, when this was replaced with a plain reeded (grooved) edge.</p>
 
    <p>Errors, overstrikes and variants of these coins are more common than they are in later coinage. That is because of the crude nature of the process by which they were made. Coin-making technology has come a long way since those strikes and later coins were changed less than these earlier varieties.</p>
 
    <p>Historical Significance
    Early U.S. coins were not the only coins post-colonial America had. French, Spanish and English coins circulated freely and were accepted as legal tender, and the mintage of new U.S. money did not stop these from circulating. The half dollar circulated but competed against these coins, particularly these early Bust half dollars. These were important because they were among the first U.S. coins minted, but in terms of day-to-day living at the time of their mintage, their impact was muted. It was not till far later that foreign coinage was banned from use as legal tender and U.S. coins became the standard.</p>
 
    <p>Numismatic Value
    Early half dollars can be hard to find, but later mintages are more common. Some early specimens in the Capped Bust series are available for a reasonable price in lower grades. Higher grades can get prohibitively expensive fast. Flowing Hair and Draped Bust examples in higher grades are out of range of all but the most dedicated and well-heeled collectors, and even low-grade examples cost hundreds of dollars. Some Capped Bust overstrikes are extremely hard to find and command a massive premium at auction.</p>
 
    <p>The higher populations of later series like the Liberty Seated make them a much easier prospect for intermediate collectors. Early examples of U.S. coinage including the half dollar are not easy to get one’s hands-on.</p>
 
    <p>Expand your collection today and shop our assortment of Early Half Dollars (1794-1836).</p>"
]
,
[
    "category_id" => 43,
    "description" => "<p>The half dollar has been struck with few interruptions from the beginning of U.S. coinage to the present day. These were sanctioned originally in the Coinage Act of 1792, and the half dollar has been one of the foundational coins of the U.S. monetary system till it fell out of favor in the late 20th century. Half dollars were first minted in 1794. The Franklin half dollar was struck from 1948 to 1963 when it was phased out in favor of the Kennedy half dollar mere months after his assassination.</p>
 
    <p>Franklin Half Dollar Design</p>
 
    <p>The Franklin half dollar was the brainchild of Mint director Nellie Tayloe Ross, who signed off on it because of her love of Benjamin Franklin. Ross wanted to get Franklin onto a coin, and with the half dollar, she had her chance.</p>
 
    <p>John R. Sinnock was the chief engraver of the mint at the time. Ross mandated that he start to work on new designs for the half dollar in 1947, and he did. But Sinnock died on May 14, 1947, and his designs were not complete. Gilroy Roberts took his place and finished the design, but Sinnock’s initials still went on the design when it was finished. Sinnock designed the Roosevelt dime as well.</p>
 
    <p>The Franklin obverse was based on Sinnock’s earlier work creating a medal with Franklin’s face. The reverse was based on a sketch by John Frederick Lewis. It showed the Liberty Bell, crack and all. There was some opposition to showing the crack on the bell, but in the end, the reverse went ahead as created.</p>
 
    <p>Franklin halves were composed of 90% Silver and not affected by the 1964 change in Silver coinage, as they stopped being struck before the change. Franklins were not struck in huge numbers until 1962 when demand increased as coins began to be hoarded. Franklin half dollars decreased in quality in the late 1950s due to the master die wearing down.</p>
 
    <p>There was some controversy about Sinnock’s initials on the coin, as this was during the Cold War while Joseph Stalin was in power in the U.S.S.R. Some thought Sinnock’s initials were those of Stalin, but the Mint resisted change. The Franklin ran with only minor changes until the Kennedy half dollar was pushed through quickly after John F. Kennedy’s assassination.</p>
 
    <p>Historical Significance
    The Franklin half dollar series was the last U.S. circulating half dollar to be made of 90% Silver for its entire run. After the 1964 Kennedy half dollar, Silver coins were not struck for circulation in 90% Silver. The half dollar was debased to 40%, and other Silver coins such as dimes and quarters were changed to cupro-nickel.</p>
 
    <p>Numismatic Value
    Franklin halves are common, particularly in the later dates which were often hoarded. There are a few dates with higher grades or errors that are harder to find, including the 1949-D. There are only a few dates and a few varieties in this series, making it easier to collect than some other U.S. issues.</p>
 
    <p>Expand your collection today and buy a Franklin Half Dollar (1948-1963).</p>"
]
,
[
    "category_id" => 44,
    "description" => "<p>The Kennedy half dollar is an iconic American coin that can trace its genesis back to mere hours after the famous president’s assassination in November of 1963. These coins were struck in massive numbers, but few people have ever seen them in circulation. The Kennedy’s place in U.S. numismatic history is unique due to its timing in relation to the U.S. stopping business strikes of Silver coins. It’s worth an examination to see what these exciting coins are worth.</p>
 
    <p>The Kennedy Half Dollar’s History</p>
 
    <p>The half dollar is a nearly obsolete denomination now, but it was one of the foundational coins established in the Coinage Act of 1792. One of the most famous half dollars was struck in the aftermath of the Kennedy assassination. The Kennedy half dollar was approved for circulation within days of the assassination as a memorial to the dead president, and it saw massive mintages for the first few years. Mintages trailed off as the denomination got less popular and everyone who wanted a coin by which to remember President Kennedy had already saved one, and by 2002 the coin was no longer struck for circulation.</p>
 
    <p>In terms of numbers, though, the Kennedy had become by far the most popular half dollar ever in American history by that point. Over 2.5 billion were struck, and though the Silver boom of 1979 through 1980 saw some destroyed and melted, the overwhelming majority of them still survive today.</p>
 
    <p>The Kennedy was struck during the transition between Silver coinage (up to 1964) and modern clad (1965 and beyond) circulating coins. The 1964-dated issue was struck of 90% Silver and 10% Copper, the same alloy previous Silver coins had been struck from. The coins issued between 1965 and 1970 were made from 40% Silver and 60% Copper. This composition continued until 1971, when they were replaced by a cupro-nickel alloy over a Copper core. The 1971-D and 1977-D have a few that were struck from Silver in error, but they are very rare.</p>
 
    <p>The design had Kennedy’s face on the obverse with the Presidential Coat of Arms on the reverse. In 1975 and 1976, in order to honor our nation’s bicentennial celebrations, a special reverse showing Independence Hall was used and the dating was changed to “1776—1976”. The previous reverse then resumed as did the use of the current date and this reverse has been used to the present day.</p>
 
    <p>Collectible Value of Kennedy Half Dollars</p>
    
    <p>Many Silver coins struck before 1964 are not just collectible for their numismatic value, but for their precious metal content. The 1964 Kennedy half is the only 90% Silver Kennedy and as such it always retains its intrinsic metal value as well as any collectible value should the coin have been saved in a high state of preservation. The pre-1971 issues (1965 to 1970) are valued for their 40% silver content, but they are not as popular as the 90% Silver issue.</p>
 
    <p>Despite the lower Silver content, these coins were hoarded in huge numbers. The Treasury had a large stockpile of Silver. Still, the vast initial mintages were rapidly depleting it even with subsidiary coins like the dime and quarter now being struck in cupro-nickel. Almost 430 million Kennedy half dollars were struck with a 1964 date, which outpaced the entire Franklin series mintage in a single year.</p>
 
    <p>Silver stockpiles continued to deplete, and eventually, the switch to clad coinage stopped the hoarding of regular circulating half dollars. At that point, the only Silver coins available were Silver proofs, which have been struck from that point to the present and for which the U.S. Mint charges a premium for the Silver content and their special handling and packaging.</p>
 
    <p>One of the most common uses of these half dollars was in casinos in slot machines, which changed as casinos began switching their slot machines to more electronic versions that did not use coins whatsoever. Other than the casinos, the Kennedy half was rarely seen in commerce, and most people have probably never come across one in the wild.</p>
 
    <p>Because of this lack of circulation, Kennedy half dollars in good condition are very common. The Red Book, the most common coin collecting resource, doesn’t even list a grade below MS-60 in its pricing chart for the Kennedy half. Premiums on these are very low, and type sets are easy to assemble. The clad examples are usually very inexpensive, only costing a few dollars. The 1964 is a little more expensive, but still very reasonable.</p>
 
    <p>There are a few expensive examples. The lowest mintage is the 1998 matte finish Kennedy half, with a little over 60,000. The Accented Hair variety is a little rarer as well, and some of the 1966 half dollars have missing initials. Some 1964 Special Mint Set half dollars can be found, but they are extremely rare. Some Kennedy half dollars saved in an unusually high state of preservation may also command significant premiums. Most of the Kennedy half dollar coins are not rare, expensive or hard to find, though.</p>
 
    <p>APMEX keeps a large variety of coins on hand, and if you’re hunting for a specific coin, we can help. You can buy or sell your rare and collectible coins with us as your partner. Contact us today if you want to sell your coins.</p>
 
    <p>If you are looking to expand your collection, shop our assortment of Kennedy Half Dollars.</p>"
]
,
[
    "category_id" => 45,
    "description" => "<p>The half dollar has been struck with few interruptions from the beginning of U.S. coinage to the present day. These were sanctioned originally in the Coinage Act of 1792, and the half dollar has been one of the foundational coins of the U.S. monetary system for most of the time until it fell out of favor in the 20th century. They were first minted in 1794. The Liberty Seated variety ran from 1839 to 1891, when it was replaced by the Barber.</p>
 
    <p>Seated Liberty Half Dollar Design</p>
 
    <p>The half dollar was struck for circulation first a few years after its smaller sibling the half dollar and was based on the same planchet. Early half dollars were struck from 89.24% Silver and 10.76% Copper, an alloy that was slightly modified at the time of the Liberty Seated release. The alloy was changed at that point to 90% Silver, a percentage it would retain until the demise of most U.S. Silver coinage in 1964.</p>
 
    <p>The Liberty Seated series had a picture of Liberty sitting on a rock, holding a Liberty pole with a Phrygian cap on the top (this symbol of freedom can be seen prominently in Capped Bust coins). The reverse on smaller coins had a wreath of laurel leaves through 1860 and on larger coins such as the quarter, half dollar and dollar this design had an eagle. After 1860 the wreath was expanded to include other plants.</p>
 
    <p>This coin saw a number of small variations over the course of its minting, including different number sizes, changes to the drapery around Liberty, the number and size of stars and more. One variety of note is the arrow by the date, which indicated a slight change in the size of the coin to bring the intrinsic value of the metal more in line with the face value. This change occurred from 1853 to 1855 and from 1873 to 1874. After a couple of years, they were changed back to the variant they had been before the arrows were implemented, as this was only a temporary measure to get people used to the changed coinage. The Liberty Seated survived through the Civil War, though many hoarded coins during this time period.</p>
 
    <p>These coins stayed in production till 1891, but changes in public approval and a push towards more interesting coinage led to the redesign of all varieties that could be legally altered at the time, including the half dollar. This created the Barber series.</p>
 
    <p>Historical Significance</p>
 
    <p>Early U.S. coins were not the only coins post-colonial America had. French, Spanish and English coins circulated freely and were accepted as legal tender, and the mintage of new U.S. money did not stop these from circulating. The half dollar circulated but competed against these coins, particularly these early Bust half dollars. These were important because they were among the first U.S. coins minted, but in terms of day-to-day living at the time of their mintage, their impact was muted.</p>
 
    <p>The Coinage Act of 1857 occurred while the Liberty Seated was being minted. Businesses that had previously accepted any form of money as legal tender now would only accept U.S. coins, and that drove demand far exceeding that of previous years. That set the stage for our current monetary system.</p>
 
    <p>Numismatic Value</p>
    
    <p>Liberty Seated half dollars are available for a reasonable price in many years in low grades, but the price rises rapidly for higher grades. There are some very expensive and rare examples from branch mints, especially the unique Carson City variants.</p>
 
    <p>The higher populations of series like the Liberty Seated as opposed to earlier half dollars make them a much easier prospect for intermediate collectors. Early examples of U.S. coinage including the half dollar are not easy to get one’s hands-on.</p>
 
    <p>Expand your collection today and shop our assortment of Liberty Seated Half Dollars (1839-1891).</p>"
]
,
[
    "category_id" => 46,
    "description" => "<p>The half dollar has been struck with few interruptions from the beginning of U.S. coinage to the present day. These were sanctioned originally in the Coinage Act of 1792, and the half dollar has been one of the foundational coins of the U.S. monetary system for most of the time until it fell out of favor in the 20th century. They were first minted in 1794. The Liberty Walking variety ran from 1916 to 1947, when it was replaced by Franklin.</p>
 
    <p>Walking Liberty Dollar Design</p>
 
    <p>The Liberty Walking came after the Barber, and it ran from 1916 to 1947. These coins were created by Adolph A. Weinman as a replacement for the Barber designs because the director of the U.S. Mint, Robert W. Woolley, thought that the 25-year minimum for a new coin design was actually a mandate to create a new coin when the time period was up.</p>
 
    <p>Woolley and the Commission of Fine Arts worked together on the Silver coins including the dime, quarter and half dollar. Many were agitating for the replacement of the Barber coins as part of an ongoing refresh to make the coins more beautiful. Not all were pleased by this. Chief Engraver Barber, long an advocate for practicality over aesthetics, provided some roadblocks to the process though he eventually did put the designs through.</p>
 
    <p>The original suggestion had been for the Mint to create new designs, and Barber had done so. The commission did not appreciate the sketches Barber submitted and recruited sculptors to create designs. Adolph Weinman, Hermon MacNeil and Albin Polasek were the three selected. Weinman’s designs were used in their entirety for the dime and the half dollar. MacNeil designed the quarter, and none of Polasek’s designs were selected.</p>
 
    <p>The new coins were publicly announced on March 3, 1916. With these design refreshes, all American coins would have a new design. This was also the first time that there was a clearly different design between all different coins, as many of the older designs were used for multiple coins with minor modifications.</p>
 
    <p>Weinman’s design shows Liberty walking and holding a bundle of branches with an American flag over her shoulder and the sun behind her. The reverse is an eagle on a mountaintop. The coins had a higher relief than many other coins of the time and striking was challenging. Barber and other proponents of practical coinage concerns over aesthetics were not fans of the new designs created by sculptors who were not necessarily familiar with the ins and outs of the striking process. There were issues with the dime and the half dollar when they were first struck, as the edge had a “fin” or raised lip that did not work well with vending machines. Barber created modified designs, but after intercession from others, the design was struck as-is with a slightly lower relief and some modifications to the strike.</p>
 
    <p>Historical Significance</p>
 
    <p>The Liberty Walking holds a special place in the American psyche due to its association with World Wars I and II. Adolph A. Weinman’s design for the obverse of the coin was so iconic it has been reused for other coins including the famous Eagle bullion coins. The redesign of the half dollar coincided with the redesign of the last few coins which had not had a refresh, and this was the time period where every coin first had a unique design.</p>
 
    <p>Numismatic Value</p>
    
    <p>Condition for these plays a key role in their value, as many were well circulated. In general, the older this coin is the more valuable it is. Branch mint versions are also more valuable than regular issues, particularly in high grades and early issues.</p>
 
    <p>Expand your collection today and shop our assortment of Walking Liberty Half Dollars (1916-1947).</p>"
]
,
[
    "category_id" => 47,
    "description" => "<p>The dollar was one of the foundational denominations of American coinage from the very beginning, and though it has fallen out of favor today it played a huge role in the monetary system of previous years. These coins were sanctioned originally in the Coinage Act of 1792 and were first minted in 1794.</p>
 
    <p>Flowing Hair and Bust Dollar Design</p>
 
    <p>Dollars were one of the several denominations authorized by the Coinage Act of 1792. Early dollars, as with all other U.S. Silver coinage of the time, used an 89.24% Silver and 10.76% Copper composition, an alloy that was slightly modified in future releases.</p>
 
    <p>The first dollars were struck in 1794. This design, called the Flowing Hair and engraved by Robert Scot, was shared with the half dime and half dollar coins of the same time period and ran from 1794 to 1795. Few of these survive, and the ones that did are rare and expensive in higher grades.</p>
 
    <p>The Draped Bust design was created by Robert Scot, the chief engraver of the U.S. Mint at the time, and ran from 1795 to 1804. As part of a redesign of currently circulating U.S. coinage, Scot changed the reverse wreath to an olive wreath symbolizing peace while keeping the eagle. He also updated Liberty’s design to add a ribbon as well as adding a drape-like garment to the bust (which gives this design its name). These ran with two different reverses and minor changes, including variable numbers of stars. There are also overstrikes that can be found in some years.</p>
 
    <p>Errors, overstrikes and variants of these coins are more common than they are in later coinage. That is because of the crude nature of the process by which they were made. Coin-making technology has come a long way since those strikes and later coins were changed less than these earlier varieties.</p>
 
    <p>Historical Significance</p>
 
    <p>Early U.S. coins were not the only coins post-colonial America had. French, Spanish and English coins circulated freely and were accepted as legal tender, and the mintage of new U.S. money did not stop these from circulating. The dollar circulated but competed against these coins, particularly these early Bust dollars. These were important because they were among the first U.S. coins minted, but in terms of day-to-day living at the time of their mintage, their impact was muted. It was not till far later that foreign coinage was banned from use as legal tender and U.S. coins became the standard.</p>
 
    <p>Spanish pieces of eight were the most common replacement for the dollar coin, as the dollar was actually based on these common circulating coins. They were very close in size and value but the difference in value between them was the basis for problems down the line and was a direct cause of the stoppage of Silver dollar production between 1804 and 1836.</p>
 
    <p>At this point, the Mint stopped creating Silver dollars. The Silver dollar had been trading at parity with the Spanish piece of eight, which created a small market for seigniorage, or making money off the difference in value between the Precious Metal and the face value. Enterprising traders shipped American Silver dollars to the Caribbean for worn-out Spanish pieces of eight to put back into circulation, pocketing the difference in Silver price. Gresham’s Law that “bad money drives out good” meant that the Silver dollar would not be struck again for over thirty years.</p>
 
    <p>Numismatic Value</p>
    
    <p>Early dollars can be hard to find, but later mintages are more common. Some early specimens in the Capped Bust series are available for a reasonable price in lower grades. Higher grades can get prohibitively expensive fast. Flowing Hair and Draped Bust examples in higher grades are out of range of all but the most dedicated and well-heeled collectors, and even low-grade examples cost hundreds of dollars. Some Capped Bust overstrikes are extremely hard to find and command a massive premium at auction.</p>
 
    <p>The higher populations of later series like the Liberty Seated make them a much easier prospect for intermediate collectors. Early examples of U.S. coinage including the dollar are not easy to get one’s hands on.</p>"
]
,
[
    "category_id" => 48,
    "description" => ""
]
,
[
    "category_id" => 49,
    "description" => "<p>The dollar was indeed one of the foundational denominations of American coinage, and its early history is fascinating. Here's the text for category 49:</p>

    <p>Flowing Hair and Bust Dollar (1794-1804)</p>

    <p>The dollar was one of the foundational denominations of American coinage from the very beginning, and though it has fallen out of favor today it played a huge role in the monetary system of previous years. These coins were sanctioned originally in the Coinage Act of 1792 and were first minted in 1794.</p>

    <p><strong>Flowing Hair and Bust Dollar Design</strong></p>

    <p>Dollars were one of the several denominations authorized by the Coinage Act of 1792. Early dollars, as with all other U.S. Silver coinage of the time, used an 89.24% Silver and 10.76% Copper composition, an alloy that was slightly modified in future releases.</p>

    <p>The first dollars were struck in 1794. This design, called the Flowing Hair and engraved by Robert Scot, was shared with the half dime and half dollar coins of the same time period and ran from 1794 to 1795. Few of these survive, and the ones that did are rare and expensive in higher grades.</p>

    <p>The Draped Bust design was created by Robert Scot, the chief engraver of the U.S. Mint at the time, and ran from 1795 to 1804. As part of a redesign of currently circulating U.S. coinage, Scot changed the reverse wreath to an olive wreath symbolizing peace while keeping the eagle. He also updated Liberty’s design to add a ribbon as well as adding a drape-like garment to the bust (which gives this design its name). These ran with two different reverses and minor changes, including variable numbers of stars. There are also overstrikes that can be found in some years.</p>

    <p>Errors, overstrikes, and variants of these coins are more common than they are in later coinage. That is because of the crude nature of the process by which they were made. Coin-making technology has come a long way since those strikes, and later coins were changed less than these earlier varieties.</p>

    <p><strong>Historical Significance</strong></p>

    <p>Early U.S. coins were not the only coins post-colonial America had. French, Spanish, and English coins circulated freely and were accepted as legal tender, and the mintage of new U.S. money did not stop these from circulating. The dollar circulated but competed against these coins, particularly these early Bust dollars. These were important because they were among the first U.S. coins minted, but in terms of day-to-day living at the time of their mintage, their impact was muted. It was not till far later that foreign coinage was banned from use as legal tender and U.S. coins became the standard.</p>

    <p>Spanish pieces of eight were the most common replacement for the dollar coin, as the dollar was actually based on these common circulating coins. They were very close in size and value, but the difference in value between them was the basis for problems down the line and was a direct cause of the stoppage of Silver dollar production between 1804 and 1836.</p>

    <p>At this point, the Mint stopped creating Silver dollars. The Silver dollar had been trading at parity with the Spanish piece of eight, which created a small market for seigniorage, or making money off the difference in value between the Precious Metal and the face value. Enterprising traders shipped American Silver dollars to the Caribbean for worn-out Spanish pieces of eight to put back into circulation, pocketing the difference in Silver price. Gresham’s Law that “bad money drives out good” meant that the Silver dollar would not be struck again for over thirty years.</p>

    <p><strong>Numismatic Value</strong></p>

    <p>Early dollars can be hard to find, but later mintages are more common. Some early specimens in the Capped Bust series are available for a reasonable price in lower grades. Higher grades can get prohibitively expensive fast. Flowing Hair and Draped Bust examples in higher grades are out of range of all but the most dedicated and well-heeled collectors, and even low-grade examples cost hundreds of dollars. Some Capped Bust overstrikes are extremely hard to find and command a massive premium at auction.</p>

    <p>The higher populations of later series like the Liberty Seated make them a much easier prospect for intermediate collectors. Early examples of U.S. coinage including the dollar are not easy to get one’s hands-on.</p>

    <p>Expand your collection today and shop our assortment of Early Silver Dollars (1794-1839).</p>"
]
,
[
    "category_id" => 50,
    "description" => "<p>The dollar coin used to be one of the foundational coins of U.S. currency. These were sanctioned originally in the Coinage Act of 1792 and first minted in 1794. Though they were forced out of circulation a couple of times over the course of their run and are no longer commonly used, Silver dollar coins have proved enduringly popular with collectors even after their run in circulation ended.</p>

    <p><strong>Liberty Seated Dollar Design</strong></p>

    <p>The Liberty Seated series had a picture of Liberty sitting on a rock, holding a Liberty pole with a Phrygian cap on the top (this symbol of freedom can be seen prominently in Capped Bust coins). The reverse on smaller coins had a wreath of laurel leaves through 1860 and on larger coins such as the quarter, half dollar and dollar this design had an eagle. After 1860 the wreath was expanded to include other plants.</p>

    <p>This coin saw a number of small variations over the course of its minting, including different number sizes, changes to the drapery around Liberty, the number and size of stars and more. The Liberty Seated survived through the Civil War, though many hoarded coins during this time period. Mintages for the Civil War years are dramatically lower than those immediately preceding the war period.</p>

    <p>These coins stayed in production till 1873, but their Silver content outweighed their face value by the 1850s and they circulated far less. New coinage laws in 1873 had no provision for the Silver dollar, and production of these dollars for U.S. circulation did not occur until the Bland-Allison Act of 1878. The Trade dollar created for export filled the gap and saw limited circulation until it was barred from the legal tender.</p>

    <p><strong>Historical Significance</strong></p>

    <p>Early U.S. coins were not the only coins post-colonial America had. French, Spanish and English coins circulated freely and were accepted as legal tender, and the mintage of new U.S. money did not stop these from circulating. The dollar circulated but competed against these coins, particularly the early Bust dollars. These were important because they were among the first U.S. coins minted, but in terms of day-to-day living at the time of their mintage, their impact was muted.</p>

    <p>The Coinage Act of 1857 occurred while the Liberty Seated was being minted. Businesses that had previously accepted any form of money as legal tender now would only accept U.S. coins, and that drove demand far exceeding that of previous years. That set the stage for our current monetary system.</p>

    <p><strong>Numismatic Value</strong></p>

    <p>Liberty Seated half dollars can get expensive, and the price rises rapidly for higher grades. There are some very expensive and rare examples from branch mints, especially the unique Carson City variants. The 1870S is an extremely rare variant that can sell for over a million dollars in high grades.</p>

    <p>The higher populations of series like the Liberty Seated as opposed to earlier dollars make them a much easier prospect for intermediate collectors. Early examples of U.S. coinage including the dollar are not easy to get one’s hands-on.</p>

    <p>Expand your collection today and shop our assortment of rare coins and currency Liberty Seated Dollars (1840-1873).</p>"
]
,
[
    "category_id" => 51,
    "description" => "<p>The dollar coin used to be one of the foundational coins of U.S. currency. These were sanctioned originally in the Coinage Act of 1792 and first minted in 1794. Though they were forced out of circulation a couple of times over the course of their run and are no longer commonly used, Silver dollar coins have proved enduringly popular with collectors even after their run in circulation ended.</p>

    <p><strong>Morgan Silver Dollar Design</strong></p>

    <p>The dollar was struck for circulation first a few years after its smaller sibling the half dollar and was based on the same planchet. Early half dollars were struck from 89.24% Silver and 10.76% Copper, an alloy that was slightly modified at the time of the Liberty Seated release. The pattern coin Gobrecht dollars that preceded them kept the original composition, but by the time business strikes took place in 1840 the metal composition had been adjusted. The alloy was changed to 90% Silver, a percentage it would retain until the last Peace dollars were struck in 1935. Morgan dollars were struck from this same 90% Silver alloy.</p>

    <p>The Liberty Seated had stayed in production till 1873, but their Silver content outweighed their face value by the 1850s and they circulated far less. New coinage laws in 1873 had no provision for the Silver dollar, and production of these dollars for U.S. circulation did not occur until the Bland-Allison Act of 1878. The Trade dollar created for export filled the gap and saw limited circulation until it was barred from the legal tender. The Morgan dollar was the direct result of the Bland-Allison Act, which made the Treasury responsible for buying between $2 million and $4 million in Silver for dollar coinage each month. This lasted until 1890 when it was replaced by the Sherman Silver Purchase Act. This act specified that the Treasury should buy 4,500,000 troy ounces of Silver each month. It only required Silver dollar production for another year, though dollar coins were still struck. This act was repealed in 1893. Then in 1898, all bullion purchased while this act was in force was required to be coined into dollars.</p>

    <p>The Morgan dollar was not struck from 1904 to 1921. The Pittman act of 1918, designed as a subsidy to the Silver industry, forced the Treasury to collect many of its previously coined Silver dollars, melt them and sell them as bullion. They then purchased Silver from American mines and used it to coin a new year of the Morgan dollar in 1921.</p>

    <p>This coin was designed by George T. Morgan, the assistant engraver of the U.S. Mint at the time. Morgan’s design features the face of Liberty prominently on the obverse, and the reverse is an eagle with wings spread similar to the reverse of the Washington quarter.</p>

    <p><strong>Historical Significance</strong></p>

    <p>The Morgan dollar’s history is set against the backdrop of the wars over the monetary system of the United States. “Free Silver” advocates like famous orator William Jennings Bryan believed that a bimetallic system would usher in a new era of prosperity for the nation. Bryan gave one of the most famous political speeches of all time in an effort to stop the advance of the Gold standard with his “Cross of Gold” address. The bimetallic standard established at the beginning of the U.S. had been repealed with the 1873 Coinage Act, and the Gold standard looked all but assured.</p>

    <p>Bryan and the Free Silver advocates lost out, however, and though Silver dollars were still struck, the bimetallic system was not in the cards for the U.S. The Gold standard became formal in 1900.</p>

    <p><strong>Numismatic Value</strong></p>

    <p>Many Morgan dollars are easy to get one’s hands-on for not much over bullion price, especially in lower grades. As a general rule, branch mints are much more valuable than the main mint. There are some very rare and collectible key dates, though, including the 1889CC, the 1893S and 1901.</p>

    <p>Morgan dollars in lower condition and non-key dates can often be bought in bulk for their Silver content. Expand your collection today and shop our assortment of Morgan Silver Dollars (1878-1921).</p>"
]
,
[
    "category_id" => 52,
    "description" => ""
]
,[
    "category_id" => 53,
    "description" => "<p>The dollar coin used to be one of the foundational coins of U.S. currency. These were sanctioned originally in the Coinage Act of 1792 and first minted in 1794. Though they were forced out of circulation a couple of times over the course of their run and are no longer commonly used, Silver dollar coins have proved enduringly popular with collectors even after their run in circulation ended.</p>

    <p><strong>Peace Dollar Design</strong></p>

    <p>Under the terms of the Pittman Act, 47 percent of all Morgan dollars were returned to the mint and melted. The metal was sold as bullion to the British government, and the coins were replaced by newly-struck Silver dollars from American Silver.</p>

    <p>The numismatist lobby asked for a coin showing the peace of World War I, and though they were not able to influence the government directly Congress took action on their own and the official design was approved in 1921.</p>

    <p>The Peace dollar came from sculptor Anthony de Francisci. De Francisci was chosen by a nationwide competition in which he was the youngest entrant at age 34. He was not a very experienced coin designer at the time, but nevertheless, he was chosen by the Commission of Fine Arts. The design was changed slightly from the original, as de Francisci’s inclusion of a broken sword inflamed public opinion when it was revealed. The obverse of the coin is a Liberty based on de Francisci’s wife. The reverse is an eagle at rest with an olive branch. The coin was struck from 90% Silver.</p>

    <p>1921’s issue had a higher relief, but it was found impractical and changed in 1922. There are a few of the high reliefs still available in 1922 but they are very hard to find and extremely expensive.</p>

    <p>The Peace dollar was resurrected for one more year in 1965 (dated 1964), but plans were scrapped and all were melted down.</p>

    <p><strong>Historical Significance</strong></p>

    <p>The Peace dollar was the first time that the numismatist lobby showed major influence in the striking of a new coin. Their idea for a commemoration of the World War I peace was directly traceable to an article in the Numismatist in November 1918, and the idea took hold in the community. The 1920 American Numismatic Association convention brought the idea to the public eye.</p>

    <p><strong>Numismatic Value</strong></p>

    <p>Peace dollars are mostly available for only a small premium over Silver price, but there are a few key dates that can be much more expensive, particularly in higher grades. In particular, the 1921 and 1922 high relief coins are rare and very hard to get one’s hands-on. Many of them are available in bulk in lower grades for their Silver content. Expand your collection today and shop our assortment of Peace Dollars.</p>"
]
,
[
    "category_id" => 54,
    "description" => ""
]
,
[
    "category_id" => 55,
    "description" => ""
],
[
    "category_id" => 56,
    "description" => ""
],
[
    "category_id" => 57,
    "description" => ""
],
[
    "category_id" => 58,
    "description" => "<p>The Three Dollar Gold piece was designed by Chief Engraver of the US Mint James B. Longacre. Authorized by the Act of Congress of February 21, 1853, the Act authorized the production of Three Dollar coins to compete in international trade.</p>

    <p>Longacre’s coin depicted an allegorical presentation of Miss Liberty, facing left, wearing a Native American headdress. Around Miss Liberty would be the words “UNITED STATES OF AMERICA.” The reverse of the coin would feature an agricultural wreath (Wheat, corn, cotton and tobacco) with the denomination inside “3 DOLLARS” on two lines and below the denomination would be the DATE.</p>

    <p>The Philadelphia Mint struck 138,618 coins that initial year (1854) while Dahlonega struck a minuscule 1,120 coins and the New Orleans mint struck 24,000 pieces. Dies were sent to the Charlotte Mint but they were not used for some inexplicable reason. Although the three southern mints had access to the dies, no additional coins were struck by them. The San Francisco Mint fared only marginally better as it struck 6,600 coins with an S mintmark in 1855, 34,500 coins in 1856, 14,000 coins in 1857, 7,000 coins in 1860 and only ONE coin is known to have been struck in 1870!</p>

    <p>The main mint in Philadelphia, after its initial striking of over one hundred thousand coins in 1854, curtailed production considerably and quickly. 1856 had 26,010 coins struck, 1857 had 20,891 minted, 1858 fell off to 2,133 coins, 1859 upped production to 15,558 pieces and 1869 minted only 7,036 coins. Between 1860 and 1870, Philadelphia production dropped to less than 10,000 pieces annually. The coins were not in demand in the urban East and circulated little in the West, except some in California, where all gold coins were scarce and welcomed. The American public thought it too close in size and value to the much more well-established $2.50 Dollar Quarter Eagle, so the coin was actively shunned.</p>

    <p>Between the 1870s and 1889, the last year of striking, only two years saw more than 6,000 coins struck with 41,800 coins dated 1874 and 82,304 coins that were dated 1878. These are the two “common years” but none are actually common as even these two dates were melted in quantity by the Mint when the coins were redeemed. 1881, 1883 and 1885 each saw mintages of less than one thousand coins making them scarcer dates for today’s collectors.</p>"
]
,
[
    "category_id" => 59,
    "description" => "<p>The Three Dollar Gold piece was designed by Chief Engraver of the US Mint James B. Longacre. Authorized by the Act of Congress of February 21, 1853, the Act authorized the production of Three Dollar coins to compete in international trade.</p>

    <p>Longacre’s coin depicted an allegorical presentation of Miss Liberty, facing left, wearing a Native American headdress. Around Miss Liberty would be the words “UNITED STATES OF AMERICA.” The reverse of the coin would feature an agricultural wreath (Wheat, corn, cotton and tobacco) with the denomination inside “3 DOLLARS” on two lines and below the denomination would be the DATE.</p>

    <p>The Philadelphia Mint struck 138,618 coins that initial year (1854) while Dahlonega struck a minuscule 1,120 coins and the New Orleans mint struck 24,000 pieces. Dies were sent to the Charlotte Mint but they were not used for some inexplicable reason. Although the three southern mints had access to the dies, no additional coins were struck by them. The San Francisco Mint fared only marginally better as it struck 6,600 coins with an S mintmark in 1855, 34,500 coins in 1856, 14,000 coins in 1857, 7,000 coins in 1860 and only ONE coin is known to have been struck in 1870!</p>

    <p>The main mint in Philadelphia, after its initial striking of over one hundred thousand coins in 1854, curtailed production considerably and quickly. 1856 had 26,010 coins struck, 1857 had 20,891 minted, 1858 fell off to 2,133 coins, 1859 upped production to 15,558 pieces and 1869 minted only 7,036 coins. Between 1860 and 1870, Philadelphia production dropped to less than 10,000 pieces annually. The coins were not in demand in the urban East and circulated little in the West, except some in California, where all gold coins were scarce and welcomed. The American public thought it too close in size and value to the much more well-established $2.50 Dollar Quarter Eagle, so the coin was actively shunned.</p>

    <p>Between the 1870s and 1889, the last year of striking, only two years saw more than 6,000 coins struck with 41,800 coins dated 1874 and 82,304 coins that were dated 1878. These are the two “common years” but none are actually common as even these two dates were melted in quantity by the Mint when the coins were redeemed. 1881, 1883 and 1885 each saw mintages of less than one thousand coins making them scarcer dates for today’s collectors.</p>"
]
,
[
    "category_id" => 60,
    "description" => "<p>Liberty Head Dollars are extremely popular and widely sought-after American coins. The first dollar coin used in the United States was minted in Gold in 1849. The Liberty Head Gold dollars were very small, only 13 millimeters wide, and are some of the smallest coins made for circulation in the U.S.</p>

    <p>This Gold coin was minted from 1849 to 1854, and in those six years different rarities formed from mostly the Charlotte (C) and Dahlonega (D), making those coins with slight variations with less than 10,000 minted of each issue scarcer and more valuable. The Philadelphia and New Orleans (O) mints also struck these Liberty Head Dollars, but they produced far more coins, making these mintages not as valuable as their Charlotte and Dahlonega counterparts.</p>

    <p>Overall, the Liberty Head Dollars are hard to find today, with only a few hundred surviving coins available from the scarcer issues.</p>

    <p>Below is a chart that represents the pricing data for U.S. $1.00 Liberty Head Gold Dollars by type. These prices are shown for typical examples of the coins since rarer dates and mintmarks cost more.</p>

    <p>Three different types of Liberty Head Dollars are distinguished by various factors. We explore the three types and what makes them unique below.</p>

    <p>$1.00 Gold Coins – Liberty & Indian Heads</p>

    <p>There are 3 different types of $1.00 Gold coins:</p>

    <p>Type 1 – 1849 to 1854 – Liberty Head</p>
    <p>Type 2 – 1854 to 1856 – Indian Head or Indian Princess Head</p>
    <p>Type 3 – 1856 to 1889 – Indian Head or Indian Princess Head</p>

    <p>You can see that all three styles have overlapping dates of mintage. Types 1 and 2 have coins dated 1854, while Types 2 and 3 both have coins dated 1856. The designs were changed from Type 1 to Type 2 because these coins were too small for commerce.</p>

    <p>Type 1 coins are all Liberty Head. They are the smallest of the 3 as they are only 13mm in size. The design looks like this:</p>

    <p>Type 2 coins are Indian Head (sometimes called Indian Princess Head). They are larger at 15mm. The design looks like this:</p>

    <p>The depiction of Miss Liberty displays she is wearing a headdress of feathers, which is why it is called the “Indian Head” or “Indian Princess Head.” The reverse design is also different and more ornate.</p>

    <p>Type 3 coins are also called “Indian Head,” but the size of head is LARGER than on the Type 2 coins. These coins are the same size, so the difference is in the style and size of the head. The design appears like this:</p>

    <p>Comparing a Type 2 next to a Type 3 – the portrait is larger; the headdress is different and not as slanted:</p>
    <p>Type 2</p>
    <p>Type 3</p>

    <p>The design changed because the Type 2 coins had a more ornate design in the headdress. Many Type 2 coins were not well-struck because of the design. Type 2 coins are the scarcest type.</p>"
]

,
[
    "category_id" => 61,
    "description" => ""
],
[
    "category_id" => 62,
    "description" => ""
],
[
    "category_id" => 63,
    "description" => ""
],
[
    "category_id" => 64,
    "description" => "<p>The Indian Head quarter eagle ran from 1908 to 1929. The design was created by Bela Lyon Pratt, apprentice to the famous Augustus Saint-Gaudens who had previously created the eagle and double eagle designs. This was during the Teddy Roosevelt-era refresh of U.S. coinage, and the quarter and the half eagle had unique and beautiful designs that were unprecedented and one of a kind in American coins.</p>

    <p><strong>Indian Head Quarter Eagle Design</strong></p>

    <p>Gold coins were mandated by the Coinage Act of 1792, with the quarter eagle, half eagle, and eagle finding their genesis in that act. These coins were struck from 90% Gold and 10% Copper.</p>

    <p>The quarter eagle and half eagle were originally meant to be the same as the double eagle design, but the inscriptions were a challenge to fit on the smaller coins. Saint-Gaudens’s design was made for the larger coins, and as he had died not long after completing his work on the eagle and the double eagle, it fell to one of his students to complete it. Bela Lyon Pratt was directed to make a new Gold coin.</p>

    <p>The design is unique in American coinage. The Native American man in the headdress on the obverse stood out because few other coins in U.S. history have had Native Americans in the design, with the exception of the Buffalo nickel, the Indian Head cent, and the Sacajawea dollar. The reverse of the coin was a perched eagle.</p>

    <p>Unlike all other U.S. coins, the surface design was engraved, not raised. This addressed the relief issues that the Saint-Gaudens coins had dealt with, and though Chief Engraver Barber made modifications to the design, they were not as sweeping as the changes made to the Saint-Gaudens coins to make them practical for regular use.</p>

    <p>This design was not saved and circulated much, and the public thought that the recessed surfaces might collect dust and dirt. Though this series was passed around as gifts by some, the demand was low enough that they stopped being minted in 1915 and only resumed in 1925. Eastern states saw less circulation than Western states, which kept Precious Metal coinage at the center of their commerce well after paper currency became the instrument of choice out East.</p>

    <p><strong>Historical Significance</strong></p>

    <p>The quarter eagle, half eagle, and eagle were foundational coins for commerce, though high-value. The value of these coins was linked to the Precious Metal used for the main coin of the denomination, with the eagle being the base coin for Gold, the dollar being the base coin for Silver, and the cent being the base coin for Copper.</p>

    <p>This continued until base metal coins became a part of American coinage, which marked the beginning of the end for Precious Metal coins. Though the nickel came in the mid-19th century, it marked the beginning of a trend that would make its way through the middle of the 20th: the removal of Precious Metal from circulating coins. Eagles became a thing of the past earlier than other denominations, but they were not the only coin to fall by the wayside.</p>

    <p><strong>Numismatic Value</strong></p>

    <p>Eagles of all types have a high value due to their Gold content, but some have a very high premium. Even in low grades, the Indian Head carries a premium higher than one might think because of low mintages and particularly low surviving populations. Many were melted. The 1911D is an extremely rare example of an Indian Head quarter eagle and is worth a fair bit of money.</p>"
]
,
[
    "category_id" => 65,
    "description" => "<p>The Liberty Head quarter eagle ran from 1840 to 1907. The Liberty head design was also known as the “Coronet Head,” and it was created by Christian Gobrecht. The design with minor modifications was used for the quarter eagle, half eagle, and eagle during their run.</p>

    <p><strong>Liberty Head Quarter Eagle Design</strong></p>

    <p>Gold coins were mandated by the Coinage Act of 1792, with the quarter eagle, half eagle, and eagle finding their genesis in that act. These coins were struck from 90% Gold and 10% Copper, though some of the Gold used at Dahlonega had a bit of Silver in the alloy that gave them a greenish tinge.</p>

    <p>The obverse of the coin is Liberty wearing a coronet. The reverse of the coin is a heraldic eagle. The design was created by well-known coin designer Christian Gobrecht, creator of the Liberty Seated design, the half-cent, the large cent, and the Gobrecht dollar. Gobrecht was the third Chief Engraver of the U.S. Mint, and he set the tone for much of the coinage that was struck around his time in office.</p>

    <p>Gobrecht’s design for the eagles had a fairly long run as U.S. coinage goes. These designs were well-liked and Gold coins saw widespread use. For the most part, these ran unchanged. There were a couple of changes including some slight modifications to the size of some design elements on the reverse. The quarter eagle had no motto, but the half eagle and the eagle ran without a motto in their earlier years and then had a motto added later in their run.</p>

    <p>The quarter eagle had one small run of its own with a mark of CAL above the eagle on the reverse.</p>

    <p>All eagle variants in this era were made with a Gold content of 90%.</p>

    <p><strong>Historical Significance</strong></p>

    <p>The quarter eagle, half eagle, and eagle were foundational coins for commerce, though high-value. The value of these coins was linked to the Precious Metal used for the main coin of the denomination, with the eagle being the base coin for Gold, the dollar being the base coin for Silver, and the cent being the base coin for Copper.</p>

    <p>This continued until base metal coins became a part of American coinage, which marked the beginning of the end for Precious Metal coins. Though the nickel came in the mid-19th century, it marked the beginning of a trend that would make its way through the middle of the 20th: the removal of Precious Metal from circulating coins. Eagles became a thing of the past earlier than other denominations, but they were not the only coin to fall by the wayside.</p>

    <p><strong>Numismatic Value</strong></p>

    <p>Eagles of all types have a high value due to their Gold content, but some have a very high premium as well. High-grade specimens of the Liberty Head are very expensive indeed. Most of the more expensive variants come from the branch mints, but there are errors and rarities that go for much more. These will often cost hundreds or even thousands of dollars even in low grades.</p>"
]
,
[
    "category_id" => 66,
    "description" => ""
]
,
[
    "category_id" => 67,
    "description" => "<p>In 1813, the only US gold coins that were being struck were the $5.00 Gold Half Eagles. The $2.50 Quarter Eagles were last struck in 1808 and the $10.00 Eagles were no longer struck after 1804. The Half Eagle became the workhorse coin for the US Mint. At this time, it was profitable to melt the current gold coinage and much of it was destroyed via the melting pot.</p>

    <p>John Reich’s Capped Head Left design was immediately popular. The head of Miss Liberty was larger (the longer bust portrait on the Draped Bust design was reduced) and a closer facial portrait was created. Miss Liberty, facing left, was surrounded by 13 six-pointed stars with the date directly below her bust. The reverse was virtually unchanged from the prior design.</p>

    <p><strong>(1813 $5.00 Gold Half Eagle, Capped Head to Left, by John Reich – Obverse [left] – Reverse [right].)</strong></p>

    <p>Reich’s design was used from 1813 until 1834. Johan Reich, upset due to lack of pay, praise, and advancement at the Mint, left their employ in 1817, but his design lived on. It was extensively copied in 1818 by Robert Scot, the Chief Engraver who had driven Reich from the Mint.</p>

    <p>In 1829, the design was reworked by William Kneass, and the diameter of the coin was reduced from 25mm to 22.5mm. All of the lettering, date, and stars were reduced in size to look proportionate. Now the portrait appeared in higher relief, and a beaded border replaced the denticles that had been part of Reich’s original design.</p>

    <p>In 1813, the first year of striking, 95,428 coins were struck. But the next year, in 1814, a mere 15,454 coins were issued. While a scant 535 coins dated 1815 were reportedly struck, only 11 coins are known. That is the first year of the rarity of this series.</p>

    <p>In the 1820s, mintages varied from 263,806 coins in 1820 to a minuscule 14,485 coins in 1823. But three years stand out in their extreme rarity. In 1822, 17,796 coins were minted but only three examples are known. This is a great rarity in any grade.</p>

    <p>Although 29,060 coins dated 1825 were struck, only two 1825 coins with a complete 5 Over 4 in the date are known.  The rest of the mintage has the 5 struck over a partial 4 in the date. The final rarity is 1828 with the 8 over 7 in the date, of which only 5 have been discovered.</p>"
]
,
[
    "category_id" => 68,
    "description" => "<p>Chief Engraver of the US Mint, William Kneass, was told by the Director of the Mint, Samuel Moore, to redesign the $5 Gold Liberty Half Eagle coin. Kneass’ Miss Liberty was younger, thinner, had more hair and had a more masculine appearance. Kneass designed Miss Liberty to face left, her hair flowing down her neck. She wore a headband that was inscribed with the word “LIBERTY” on it. She was surrounded by 13 six-pointed stars and the date was directly under her bust. On the reverse, the eagle remained the same but the scroll with “E PLURIBUS UNUM” on it was removed. The legend “UNITED STATES OF AMERICA” and the denomination “5 D.” remained as previous.</p>

    <p><strong>(1834 $5 Gold Liberty Half Eagle, No Motto Variety – Obverse [left] – Reverse [right].)</strong></p>

    <p>In 1834, 657,460 coins were struck between the two varieties – a plain 4 in the date and a crosslet 4. No one knows the breakdown of each but the Crosslet 4 variety is significantly more expensive.</p>

    <p>The following year, 1835, there were 371,534 coins minted using either a script-style 8 in the date or a block-style 8. In 1836, there were 553,147 coins struck, and they also had to deal with the Script 8 and Block 8 digits in the date. By 1837, the issue of style still had not been resolved but only 207,121 coins were struck.</p>

    <p>During the final year of striking, 1838, the Philadelphia Mint’s production moved up to striking 286,588 coins. The newly-opened mints in both Charlotte (NC) and Dahlonega (GA) began to strike these coins with 17,179 coming from Charlotte and 20,583 coming from Dahlonega. As this was the first time that mintmarks were required, the “C” and “D” mintmarks were awkwardly placed on the obverse of the coin, just above the date.</p>

    <p><strong>(The 1838-C from Charlotte (NC) [left] and the 1838-D from Dahlonega (GA) [right] with OBVERSE Mintmarks above the date.)</strong></p>

    <p>The 1834 Crosslet 4 variety coin and both of the 1838 branch mint coins from Charlotte and Dahlonega are the most expensive and most difficult coins to find.</p>"
]
,
[
    "category_id" => 69,
    "description" => "<p>These coins were designed under the authority of President Teddy Roosevelt who, at the beginning of the 20th Century demanded that our boring and unartistic coinage get a make-over. Roosevelt sought out his friend Augustus Saint-Gaudens to redesign all of our gold coinage ($2.50, $5.00, $10.00 and $20.00) as well as the One Cent pieces.</p>

    <p>But Saint-Gaudens was ill and nearing the end of his life. He created the $10.00 Gold Indian Head and also his masterpiece, the $20.00 Saint-Gaudens Double Eagle gold coin. By the end of 1907, both the $10.00 Gold Eagle and $20.00 Gold Double Eagles were already in circulation. The Mint had wanted to take Saint-Gaudens $20.00 Double Eagle design and strike it in smaller $2.50 Quarter Eagle and $5.00 Half Eagle sizes. But President Roosevelt didn’t want that. He wanted new designs.</p>

    <p>Roosevelt suggested to US Mint Director Frank Leach that on small coins if the designs were lower than the background, they would give a high relief effect to the viewer. Such coin and medal designs were already being experimented with by Boston Sculptor Bela Lyon Pratt! Pratt was pressed into service and created these new incuse design coins. The Mint’s Chief Engraver, Charles E. Barber tinkered with the designs until the Mint was happy with them and they were easily reproducible.</p>

    <p>Pratt’s design was iconically American, with a male Indian (instead of Miss Liberty) wearing an Indian headdress and facing left. There were six five-pointed stars in front of him and seven five-pointed stars behind him. The word “LIBERTY” was above him and the date was below him.</p>

    <p>The reverse was dominated by an American eagle also facing left. “UNITED STATES OF AMERICA” surmounted the coin, “E PLURIBUS UNUM” was in front of the eagle while “IN GOD WE TRUST” was behind the eagle. The eagle is standing on a bunch of arrows and holding an olive branch in his left talon.</p>

    <p><strong>(1908 $5.00 Gold Indian Half Eagle by Bela Lyon Pratt. Obverse [left] – Reverse [right].)</strong></p>

    <p>This coin and its $2.50 counterpart are the first two American coins ever to have an incuse design. They met the requirements of commerce as they stack well but also their appearance pleased Roosevelt since their appearance gave the impression of a high relief design – exactly what Roosevelt wanted!</p>

    <p>In the first year, 1908, the Philadelphia mint struck 577,845 coins, while Denver struck 148,000 and San Francisco struck 82,000 coins. Coins were struck between 1908 and 1916. After 1916, which was only struck at the San Francisco Mint, production ceased as all gold and silver coins were being hoarded by the public due to World War I. No further coins were produced until 1929, and nearly all of that date were recalled and melted due to the economic recession that was in full swing.</p>"
]
,
[
    "category_id" => 70,
    "description" => "<p>Christian Gobrecht was the Second Engraver of the US Mint but in 1835, the Chief Engraver, William Kneass, suffered a stroke and was incapacitated. Gobrecht assumed the Chief Engraver’s duties in the Fall of 1835 but he did not become the Chief Engraver of the US Mint until 1840.</p>

    <p>In 1839, he was asked to redesign the $5 Gold, Classic Head, No Motto Half Eagle. Gobrecht designed a younger, thinner, more youthful Miss Liberty. Like her predecessor, she faced left, but her hair was not tousled locks. It was now neatly positioned on her head in a bun, with a braid at the end. She was still surrounded by 13 six-pointed stars with the date underneath her.</p>

    <p>The eagle on the reverse became thinner but not scrawny. She still held arrows and an olive branch in her talons with “UNITED STATES OF AMERICA” around. The denomination was now spelled out as “FIVE D.” below the eagle. The eagle’s wingspan now reached from rim to rim.</p>

    <p><strong>(Gobrecht’s new style $5 Gold Half Eagle – Variety 1 – No Motto – Obverse [left] – Reverse [right].)</strong></p>

    <p>This design remained unchanged until 1866 when a scroll with “E PLURIBUS UNUM” was added above the eagle on the reverse. The coins bearing that modification are known as “Variety 2 – With Motto.”</p>

    <p><strong>(Gobrecht’s Variety 2 – With  Motto – Obverse [left] – Reverse [right].)</strong></p>

    <p>The Variety-2 coins were minted from 1866 until 1908 when an entirely new design replaced them.</p>

    <p>These No Motto Half Eagles were struck at five different US Mints:
    <ul>
        <li>Philadelphia (no mint mark)</li>
        <li>Charlotte (C)</li>
        <li>Dahlonega (D)</li>
        <li>New Orleans (O)</li>
        <li>San Francisco (S)</li>
    </ul>
    Over 9 million of these coins were struck by all of the mints combined but, of course, Charlotte and Dahlonega examples are among the most expensive and desirable. The rarest Philadelphia coins are those struck during the Civil War years when hoarding across the country was inevitable and widespread. Other scarce dates from the branch mints include:
    <ul>
        <li>1842-C Small Date Variety</li>
        <li>1842-D Large Date Variety</li>
        <li>1861-C (The last coin struck at this mint before the mint was taken over by the Confederacy)</li>
        <li>1854-S Two are known but 268 were supposedly struck according to US Mint records.</li>
    </ul>
    New Orleans gold coin collectors are able to complete collections easier than those collecting Charlotte or Dahlonega coins. Proof issues from Philadelphia exist beginning in 1859, but they were struck in very small quantities – from a low of 25 coins to a high of only 80 pieces.</p>"
]
,
[
    "category_id" => 71,
    "description" => "<p>Robert Scot, who as Chief Engraver, had designed the previous design –  “Small Eagle” – a variety of Half Eagle gold coins, took a great deal of complaints about the “Scrawny Eagle” on the reverse and modified his design to be more of a “Heraldic Eagle” similar to that on the Great Seal of the United States.</p>

    <p>(Robert Scot’s “Scrawny Eagle” [left] and the Great Seal of the United States [right] after which Scot modeled his new eagle design.)</p>

    <p>Scot’s new reverse had a larger and more dramatic eagle, more befitting our new gold coinage. Curiously, unknown numbers of Half Eagles dated 1795, 1796/5, 1797 with 16 Star obverse, and 1797 with 15 Star obverse all mysteriously began to appear in commerce. These coins and those with the Small Eagle reverse circulated simultaneously and it is believed that Scot created all of these coins, hoping that his Small Eagle design would be forgotten about once these were available. All of the above coins were believed to have been struck in 1798 and the total mintages reported for the Small Eagle design may well include the coins struck with this Heraldic Eagle design.</p>

    <p>The Heraldic Eagle design utilizes the exact same obverse design with Miss Liberty wearing a “Turban-type cap” and facing right, with the date below her and stars in front of her and behind her. The motto “LIBERTY” is directly above her at the periphery.</p>

    <p>The Heraldic Eagle reverse has a larger eagle, facing left, with an American shield for a body, wings upraised, 16 six-pointed stars are above the wings, and a grouping of clouds are above the stars. The legend “UNITED STATES OF AMERICA” nearly encircles the periphery completely.  In the eagle’s right talon are arrows and in her left talon is an olive branch, representing that America is ready for war or for peace.</p>

    <p>This design received significantly more favorable commentary and was thought to represent a stronger and more mature the United States. The “heraldic eagle” coins were well-received by the public and merchants alike.</p>

    <p>(1795-dated, but struck in 1798, $5.00 Half Eagle, Capped Bust, Heraldic Eagle gold coin. Obverse [left] – Reverse [right.)</p>

    <p>Although the mintages for the Heraldic Eagle Half Eagle coins for 1795 through 1797 are unknown, the year 1798 saw the Philadelphia Mint strike 24,867 Half Eagles. There are at least four varieties of 1798-dated Half Eagle coins:
    <ul>
        <li>1798 with a Small Eagle reverse</li>
        <li>1798 Heraldic Eagle with a Small 8 in the date</li>
        <li>1798 Heraldic Eagle with a Large 8 and 13 stars on the reverse</li>
        <li>1798 Heraldic Eagle with a Large 8 and 14 stars on the reverse.</li>
    </ul>
    The year 1799 saw the mint strike 7,451 coins while in 1800 the mintage jumped to 37,628. There were no coins struck that were dated 1801 but in 1802 a whopping 53,176 coins were struck, all of which were overdates of 1802 2 Over 1. Dies were reused again in 1803 with 33,506 coins minted, again all of which are 1803 3 Over 2 overdates.</p>

    <p>The popular date, 1804, saw two distinct varieties – a Small 8 and a Small 8 Over a Large 8. These two varieties divided up the 30,475 1804-dated coins.  There were 33,183 coins struck by the Mint in 1805. In 1806 two varieties were minted with there being 9,676 coins struck with a Pointed-Top 6 and the more common Round-Top 6 which had 54,417 coins issued. In the final year, 1807, 32,488 coins were minted before the design was changed completely.</p>"
]
,
[
    "category_id" => 72,
    "description" => "<p>When William McKinley was assassinated in 1901 in Buffalo, NY, most Americans had no idea how the new president, Teddy Roosevelt, would change things. He was a doer and was not very good at accepting excuses. Roosevelt wanted action!</p>

    <p>By 1904 he began dealing with an issue that was very important to him – our coinage. Teddy was always blunt and to him, our present coinage was hideous! He didn’t hesitate to let those responsible for our coinage know he expected more and was displeased, The Chief Engraver of the Mint, Charles E. Barber, was none too pleased with the president either, as many of the current coinage designs were his own.</p>

    <p>After the election in 1904, Roosevelt made it a priority to speak to his good friend, Augustus Saint-Gaudens about changing the coinage designs. All four circulating gold coins could be redesigned, as well as the cent, all without any Congressional approval required, so Roosevelt urged Saint-Gaudens to get to work on the $10 Gold Eagle and the $20 Gold Double Eagle.</p>

    <p>The current $10 Gold Eagle was the Liberty Head design created in the 1840s by James Longacre. The design had remained unchanged for over 40 years. Saint-Gaudens instead created something more original and iconically more American. He created the head of Liberty, facing left, wearing a large Indian war bonnet. 13 six-pointed stars were arranged along the periphery of the top of the obverse while the date was centered under Liberty.</p>

    <p>The reverse had a large and majestic-looking eagle standing on a bundle of arrows and an olive branch. The legends “UNITED STATES OF AMERICA” and “E PLURIBUS UNUM” were placed above the eagle while the denomination was below her. There was no “IN GOD WE TRUST” motto on the coin as Roosevelt objected to using the deity’s name on coinage.</p>

    <p>The edge was unusual in that the 46 United States were represented by 46 raised stars on the edge. With the addition of New Mexico and Arizona in 1912, coins dated 1912 and later all now had 48 raised stars on the edge.</p>

    <p>(1907 $10 Gold Eagle Indian Head – Obverse [left] – Reverse [right].)</p>

    <p>The public liked the 1907 design but did not like that the reference to God was omitted. In 1908, the motto “IN GOD WE TRUST” was restored to the coin and placed on the reverse in front of the eagle.  The year 1908 will have coins struck both with and without the motto.</p>

    <p>The initial year of minting, 1907, saw three different varieties of coins struck. The first variety is a wire rim with a period’s version of which 500 coins were struck. A second variety was created which had a rounded rim and periods between the words in the motto “E.PLURIBUS. UNUM.” only saw 50 coins struck.  The third and final version was a no periods version in which 239,406 coins were struck.</p>

    <p>In 1908, the no motto variety saw 33,500 coins struck at Philadelphia and 210,000 struck at Denver. The Motto variety saw 341,370 coins struck at the main mint and 836,500 in Denver and 59,850 in San Francisco.</p>

    <p>As for other scarce dates, the 1911 coin struck in Denver only saw 30,100 coins struck, the 1913 coin from San Francisco had only 66,000 coins struck, the 1920-S had 126,500 coins struck, the 1930-S had 96,000 coins struck and in 1933 312,500 coins were struck with nearly all of them melted at the mint for their gold content.</p>"
]
,
[
    "category_id" => 73,
    "description" => "<p>Between the last of the Heraldic Eagles in 1804 (which were actually minted in 1834) until 1838, no new $10.00 Gold Eagle coins were struck. Eagles, when infrequently available, were often melted as there was a premium on their size and weight. But a gold coin shortage in the United States received some relief with the discovery of gold in both the Carolinas and Georgia. Now, the demand for gold was strong so the government opened mints in Charlotte (NC), Dahlonega (GA) and in New Orleans, as it was the second most important port in the United States.</p>

    <p>As the price of gold continued to rise and hoarding was rampant, the size and weight of the $10 Gold eagle were reduced. Second Chief Engraver Christian Gobrecht, who did most of the designing while the Chief Engraver, William Kneass, was suffering a stroke, designed the new eagle that would be struck in 1838.</p>

    <p>Gobrecht’s design depicted a bust of Miss Liberty, with her hair pulled back, wearing a crown with the word “LIBERTY” inscribed on it. Miss Liberty faced left and 13 six-pointed stars encircled her with the date directly beneath her bust. The reverse featured an eagle with wings outstretched and pointed upwards, holding three arrows in one talon and an olive branch in the other. Around the eagle was “UNITED STATES OF AMERICA” with the denomination “TEN D.” directly below the eagle.</p>

    <p>(1838 Liberty Head Eagle – Gobrecht’s New Design – Obverse [left] – Reverse [right].)</p>

    <p>In 1838, the initial year, only 7,200 coins were struck. In 1839, 25,801 coins were struck, all with Large Letters. There was also an unknown tiny quantity of a proof version, with Large Letters and an overdate of 1839 9/8. The known coin is extremely rare.</p>

    <p>Beginning in 1841, coins were struck at the New Orleans Mint, bearing the famed “O” mintmark. In 1855, coins from the San Francisco mint, bearing an “S” were struck as well. The last New Orleans coin of this type was struck in 1860, as the mint was occupied by the State of Louisiana and then the Confederacy after the attack on Fort Sumter in 1861. The design was struck throughout 1865, and in 1866 in San Francisco until legislation created the “Motto” variety 1866. A resurgence of religious fervor swept the country during the Civil War and our coinage was not immune to representing the current passion of the day.</p>"
]
,
[
    
        "category_id"=>  74,
        "description"=>  "<p>The first Chief Engraver of the United States Mint, Robert Scot, created all three of the designs for the first US $10.00 Gold Eagles – one obverse design and two reverse designs, a small eagle and a heraldic eagle. All three gold coin denominations used these same designs. Scott heard the public and US Mint’s criticisms of his small eagle design (called a “Scrawny Eagle”) and set about to remedy the critique.</p>

        <p>He modeled the new eagle after the design on the Great Seal of the United States. The eagle, being the central vignette, was much more majestic than in his small eagle design. It more gloriously represented the new nation.</p>

        <p>(Robert Scot’s Small Eagle design [left], the Great Seal of the United States [center] and his modified Heraldic Eagle design [right].)</p>

        <p>Scott did not modify the obverse design from his first design. He kept Miss Liberty wearing a turban-like hat, facing to her right. Above her, on the periphery, was the word “LIBERTY” and there were five six-pointed stars to the right and eight six-pointed stars to the left. The date is under Miss Liberty. The new Heraldic Eagle design had the eagle facing left, with a scroll in her mouth on which was inscribed “E PLURIBUS UNUM.”  Surrounding the eagle’s head were 13 six-pointed stars. Above the stars was a bank of clouds. Around the periphery of the coin was “UNITED STATES OF AMERICA.” No denomination was started on these early gold coins and Scott’s modified eagle was much more successful and the design received praise instead of scorn.</p>

        <p>(The Scot-modified $10 Gold Eagle with the Heraldic Eagle reverse – Obverse [left] – Reverse [right].)</p>

        <p>So during the first year of the new design – 1797 – the Philadelphia Mint struck 10,940 coins. The next year, 1798, the mint struck only 900 coins that were struck as an 8/7 overdate but had nine six-pointed stars to the left and four six-pointed stars to the right. They also struck 842 examples on the other variety for 1798, which was also an 8/7 overdate with seven six-pointed stars to the left and six six-pointed stars to the right.</p>

        <p>The year 1799 saw two varieties struck between the 37,449 coins – one with Small Obverse Stars and the other with Large Obverse Stars. They are about equal in value and no one knows exactly how many of each were struck. In 1800 production dwindled to 5,999 coins and in 1801 minting soared to 44,344 coins, the most struck in this series. No 1802-dated coins were struck. In 1803, two varieties were struck among the 15,017 coins minted – one with Small Reverse Stars and the other with Large Reverse Stars.</p>

        <p>There were a total of 3,757 eagles dated 1804 that was struck in that year. They are called “Crosslet 4” coins because they have short vertical extensions of the cross-stroke at the end. Although over 3,700 coins were minted, very few are known to exist today.</p>

        <p>In 1834, the Government intended to present a set of then-current US coins to four Asian rulers with whom they hoped to open trade relations. Although this was 30 years later, neither a $10 Gold coin nor a Silver Dollar had been struck since 1804. The US Mint Director was pressured to strike 1804-dated $10 gold eagles and silver dollars specifically for these sets. Four of each of these two coins were struck and the sets were completed and encased in wood, leather, and the four coins struck for these four sets where all of the “Plain 4” variety with no Crosslet and they were struck in Proof condition.</p>

        <p>Two sets were presented to the King of Siam and the Sultan of Muscat and Oman. But the two remaining sets were never presented as the US diplomat died of disease. The existence of all four sets is known today, with three in private hands and the fourth in a museum.</p>"
    
]
,
[
    
        "category_id"=> 75,
        "description"=> "<p>The eagle was one of the classic denominations of U.S. coinage, created in the Coinage Act of 1792. Its subsidiary denominations the quarter and half eagle were commonly coined from the beginning of U.S. coinage through the early 20th century. The double eagle was struck from the 1850s through the early 20th century.</p>

        <p>The Liberty Head double eagle ran from 1849 to 1907. The Liberty head design was also known as the “Coronet Head”, and it was created by James B. Longacre.</p>

        <p>Liberty Twenty Dollar Gold Coin Design</p>

        <p>Gold coins were mandated by the Coinage Act of 1792, with the quarter eagle, half eagle and eagle finding their genesis in that act. These coins were struck from 90% Gold and 10% Copper. The eagle had worked for years, but the double eagle was only authorized after the California Gold Rush. Gold stockpiles grew dramatically and support for a bigger denomination was strong. A bill to create a Gold dollar and double eagle passed Congress in 1849 and the Mint was authorized to create a new coin.</p>

        <p>There was some internal conflict at the Philadelphia Mint at the time. James Longacre, the Chief Engraver, was the outsider and dealt with a fair bit of harassment in his quest to create the design. It eventually went through, but the final set of dies was not completed until 1850.</p>

        <p>The head is Liberty, set in a classical style with her hair in a bun. She is wearing the coronet which gives this coin its alternate name. The reverse is a variation on the Great Seal of the United States. At the time the design was not received well, but it has retained significant collectability.</p>

        <p>The double eagle was one of the biggest mintages of coins at the time. Almost all of the Gold used for coinage was turned into double eagles. California and the western territories and states had a major shortage of Precious Metal coins.</p>

        <p>There were minor changes made to the coin over its run, but for the most part, this design ran for the whole duration. In 1904 President Roosevelt inquired about changing the design, and the following year his friend Augustus Saint-Gaudens began a new design that went into effect in 1907. This marked the end of the Liberty Head double eagle.</p>

        <p>Historical Significance</p>

        <p>The quarter eagle, half eagle and eagle were foundational coins for commerce, though high-value. The value of these coins was linked to the Precious Metal used for the main coin of the denomination, with the eagle being the base coin for Gold, the dollar being the base coin for Silver and the cent being the base coin for Copper. The double eagle was the superior denomination of the base coin for Gold, the eagle.</p>

        <p>This continued until base metal coins became a part of American coinage, which marked the beginning of the end for Precious Metal coins. Though the nickel came in the mid-19th century, it marked the beginning of a trend that would make its way through the middle of the 20th: the removal of Precious Metal from circulating coins. Eagles became a thing of the past earlier than other denominations, but they were not the only coin to fall by the wayside.</p>

        <p>Numismatic Value</p>

        <p>Eagles of all types have a high value due to their Gold content, but some have a very high premium. High-grade specimens of the Liberty Head are very expensive indeed. Most of the more expensive variants come from the branch mints, but there are errors and rarities that go for much more. 1849 is a pattern coin, and the best we can tell is that the mintage was only one. Some of the late mintages in high grades go for a lot of money. The 1870CC is so rare as to be almost impossible to find, and type sets can be hard to collect. 1886 is also very expensive and rare.</p>

        <p>Expand your collection today and shop our assortment of $20 Gold Liberty Double Eagle Coins (1850-1907).</p>"
    
]
,
[
    
        "category_id"=> 75,
        "description"=> "<p>The eagle was one of the classic denominations of U.S. coinage, created in the Coinage Act of 1792. Its subsidiary denominations the quarter and half eagle were commonly coined from the beginning of U.S. coinage through the early 20th century. The double eagle was struck from the 1850s through the early 20th century.</p>

        <p>The Saint-Gaudens double eagle is often considered the most beautiful U.S. coin. It was designed by Augustus Saint-Gaudens, a famous sculptor who had previously worked with the Mint but had run into conflicts and had refused coin commissions. Theodore Roosevelt’s friendship with Saint-Gaudens and ascent to the Presidency allowed Roosevelt to persuade him to take another stab at coin design, and the resulting double eagle ran from 1907 to 1933.</p>

        <p>Saint-Gaudens Double Eagle Design</p>

        <p>Gold coins were mandated by the Coinage Act of 1792, with the quarter eagle, half eagle and eagle finding their genesis in that act. These coins were struck from 90% Gold and 10% Copper. The eagle had worked for years, but the double eagle was only authorized after the California Gold Rush. Gold stockpiles grew dramatically and support for a bigger denomination was strong. A bill to create a Gold dollar and double eagle passed Congress in 1849 and the Mint was authorized to create a new coin.</p>

        <p>The Liberty Head had had a long and successful run, but Theodore Roosevelt wanted to modernize U.S. coinage and create works that were outstanding for beauty and design. The high-relief design of the early Saint-Gaudens coins was directly tied to Roosevelt’s wishes and diametrically opposed to the wishes of the mint, particularly Chief Engraver Barber.</p>

        <p>Saint-Gaudens had been working on an eagle design for the cent, but he adopted it for the double eagle after learning an eagle could not by law appear on the cent. The obverse of the design is Liberty holding a torch and olive branch walking across a rocky area with the U.S. Capitol and the sun behind her. The reverse is a flying eagle with the sun rising behind it. The motto was placed on the edge of the coin to make way for the other design elements.</p>

        <p>The first designs that were sent to the Mint were struck as pattern coins, and even on a special medal press, it took up to nine strokes. Circulation strikes had to be doable in one. The few ultra-high relief coins that have survived are incredibly rare and expensive. The design was knocked down to high relief, then further for successive years of coinage as it proved impractical for regular use. Chief Engraver Barber created his own low-relief version of the design which was used for some 1907 coins and all years succeeding. Barber made minor modifications to the design in addition to lowering the relief.</p>

        <p>Double eagles were struck through 1916, then stopped as Gold coins flowed back across the Atlantic due to World War I. They were struck again from 1920 through 1933. Many of the later coins in the series were reclaimed when President Franklin D. Roosevelt reclaimed Gold, and they were often melted. Though mintages were large 1933 is one of the rarest dates in the series.</p>

        <p>Historical Significance</p>

        <p>The quarter eagle, half eagle and eagle were foundational coins for commerce, though high-value. The value of these coins was linked to the Precious Metal used for the main coin of the denomination, with the eagle being the base coin for Gold, the dollar being the base coin for Silver and the cent being the base coin for Copper. The double eagle was the superior denomination of the base coin for Gold, the eagle. These saw significant circulation.</p>

        <p>This continued until base metal coins became a part of American coinage, which marked the beginning of the end for Precious Metal coins. Though the nickel came in the mid-19th century, it marked the beginning of a trend that would make its way through the middle of the 20th: the removal of Precious Metal from circulating coins. Eagles became a thing of the past earlier than other denominations, but they were not the only coin to fall by the wayside.</p>

        <p>Numismatic Value</p>

        <p>Eagles of all types have a high value due to their Gold content, but some have a very high premium. High-grade specimens of the Saint-Gaudens are very expensive indeed. Most of the more expensive variants come from the branch mints, but there are errors and rarities that go for much more. All 1907 varieties are rare and expensive, particularly the ultra-high relief pattern coin. Later dates in the series are also very rare and expensive, particularly 1933, of which many were melted.</p>

        <p>Expand your collection today and shop our assortment of $20 Gold Saint-Gaudens Double Eagle Coins (1907-1933).</p>"
    
]

    ]
];

    // Search for the description based on category ID
    $description = "Category ID not found"; // Default message if not found
    foreach ($json_data['descriptions'] as $item) {
        if ($item['category_id'] == $category_id) {
            $description = $item['description'];
            break; // Exit loop once the description is found
        }
    }

    // Return the description
    return $description;
}

add_shortcode('category_description', 'category_description_shortcode');


?>