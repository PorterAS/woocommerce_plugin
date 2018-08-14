<?php
/**
 * PorterBuddy API classes
 */

/**
 * Class Address
 */
class Address
{
	public $streetName;
	public $streetNumber;
	public $postalCode;
	public $city;
	public $country;
	public $address;
	public $address_2;

	public function __construct($address, $address_2, $postalCode, $city, $country)
	{
		$this->address = $address;
		$this->address_2 = $address_2;
		$this->postalCode = $postalCode;
		$this->city = $city;
		$this->country = $country;
		$this->formatAddress();
	}

	/**
	 * Splits the address into street name and number
	 */
	public function formatAddress()
	{
		$address = $this->address.''.$this->address_2;

		// Find a match and store it in $result.
		if ( preg_match('/([^\d]+)\s?(.+)/i', $address, $result) )
		{
			// $result[1] will have the steet name
			$this->streetName = $result[1];
			// and $result[2] is the number part.
			$this->streetNumber = $result[2];
		}
	}

	/**
	 * Returns the address as a formatted array
	 * ready for json encoding.
	 *
	 * @return array
	 */
	public function getArray()
	{
		return [
			'streetName' => $this->streetName,
			'streetNumber' => $this->streetNumber,
			'postalCode' => $this->postalCode,
			'city' => $this->city,
			'country' => $this->country
		];
	}

	public function validate()
	{
		// TODO: Validation
		// If needed, validation can be done here.
		return true;
	}
}

/**
 * Class Buddy
 */
class Buddy
{
	private $_api_key;
	private $_url;

	public function __construct($api_key, $url)
	{
		$this->_api_key = $api_key;
		$this->_url = $url;
	}

	/**
	 * Executes a API call to the availability endpoint.
	 *
	 * @param Address $originAddress
	 * @param Address $destinationAddress
	 * @param array $pickupWindows
	 * @param array $orderParcels
	 * @param array $orderProducts
	 *
	 * Returns the result from curl_exec().
	 *
	 * @return mixed
	 */
	public function checkAvailability(
		Address $originAddress,
		Address $destinationAddress,
		Array $pickupWindows,
		Array $orderParcels,
		Array $orderProducts
	)
	{
		$windows = [];
		$parcels = [];

		$request = new Request($this->_api_key, $this->_url);
		$request->setEndpoint('availability');

		foreach ($pickupWindows as $window) $windows[] = $window->getArray();
		foreach ($orderParcels as $parcel) $parcels[] = $parcel->getArray();

		$request->setPayload([
			'originAddress' => $originAddress->getArray(),
			'destinationAddress' => $destinationAddress->getArray(),
			'pickupWindows' => $windows,
			'parcels' => $parcels,
			'products' => $orderProducts
		]);

		return $request->execute();
	}

	/**
	 * Executes a API call to the order endpoint.
	 *
	 * @param Origin $origin
	 * @param Destination $destination
	 * @param array $parcels
	 * @param $product
	 * @param $courierInstructions
	 *
	 * Returns the result from curl_exec()
	 *
	 * @return mixed
	 */
	public function placeOrder(
		Origin $origin,
		Destination $destination,
		Array $parcels,
		$product,
		$courierInstructions
	)
	{
		$request = new Request($this->_api_key, $this->_url);
		$request->setEndpoint('order');

		$request->setPayload([
			'origin' => $origin->getArray(),
			'destination' => $destination->getArray(),
			'parcels' => $parcels,
			'product' => $product,
			'courierInstructions' => $courierInstructions
		]);

		return $request->execute();
	}
}

/**
 * Class Destination
 */
class Destination extends Origin
{
	public $deliveryWindow;
	public $verifications;

	public function __construct(
		$name,
		Address $address,
		$email,
		$phoneCountryCode,
		$phoneNumber,
		$deliveryWindow,
		$verifications
	)
	{
		parent::__construct(
			$name,
			$address,
			$email,
			$phoneCountryCode,
			$phoneNumber,
			null
		);
		$this->deliveryWindow = $deliveryWindow;
		$this->verifications = $verifications;
	}

	/**
	 * Returns the origin information as a formatted array
	 * ready for json encoding.
	 *
	 * @return array
	 */
	public function getArray()
	{
		return [
			'name' => $this->name,
			'address' => $this->address->getArray(),
			'email' => $this->email,
			'phoneCountryCode' => $this->phoneCountryCode,
			'phoneNumber' => $this->phoneNumber,
			'deliveryWindow' => $this->deliveryWindow->getArray(),
			'verifications' => $this->verifications
		];
	}
}

/**
 * Class Origin
 */
class Origin
{
	public $name;
	public $address;
	public $email;
	public $phoneCountryCode;
	public $phoneNumber;
	public $pickupWindows;

	public function __construct(
		$name,
		Address $address,
		$email,
		$phoneCountryCode,
		$phoneNumber,
		$pickupWindows
	)
	{
		$this->name = $name;
		$this->address = $address;
		$this->email = $email;
		$this->phoneCountryCode = $phoneCountryCode;
		$this->phoneNumber = $phoneNumber;
		$this->pickupWindows = $pickupWindows;
	}

	/**
	 * Returns the origin information as a formatted array
	 * ready for json encoding.
	 *
	 * @return array
	 */
	public function getArray()
	{
		$pickupWindows = [];

		foreach ($this->pickupWindows as $window) $pickupWindows[] = $window->getArray();
		return [
			'name' => $this->name,
			'address' => $this->address->getArray(),
			'email' => $this->email,
			'phoneCountryCode' => $this->phoneCountryCode,
			'phoneNumber' => $this->phoneNumber,
			'pickupWindows' => $pickupWindows
		];
	}
}

/**
 * Class Parcel
 */
class Parcel
{
	public $widthCm;
	public $heightCm;
	public $depthCm;
	public $weightGrams;
	public $description;

	public function __construct($widthCm, $heightCm, $depthCm, $weightGrams, $description = null)
	{
		$this->widthCm = $widthCm;
		$this->heightCm = $heightCm;
		$this->depthCm = $depthCm;
		$this->weightGrams = $weightGrams;
		$this->description = $description;
	}

	/**
	 * Returns the parcel information as a formatted array
	 * ready for json encoding.
	 *
	 * @return array
	 */
	public function getArray()
	{
		return [
			'widthCm' => $this->widthCm,
			'heightCm' => $this->heightCm,
			'depthCm' => $this->depthCm,
			'weightGrams' => $this->weightGrams,
			'description' => $this->description
		];
	}
}

/**
 * Class Request
 */
class Request
{
	private $_ch;
	private $_api_key;

	public $url;
	public $payload;

	public function __construct($api_key, $url)
	{
		$this->_api_key = $api_key;
		$this->url = $url;
		$this->_ch = curl_init();
		curl_setopt($this->_ch, CURLOPT_CUSTOMREQUEST, 'POST');
	}

	/**
	 * Set the endpoint of the request.
	 * The endpoint string is appended to the url.
	 *
	 * @param string $endpoint
	 */
	public function setEndpoint($endpoint)
	{
		curl_setopt($this->_ch, CURLOPT_URL, $this->url.$endpoint);
	}

	/**
	 * Set the request payload.
	 * Accepts PHP-array and encodes to json.
	 *
	 * @param array $payload
	 */
	public function setPayload($payload)
	{
		$this->payload = json_encode($payload);
		curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $this->payload);
	}

	/**
	 * Execute the API-call.
	 * Returns the result from curl_exec().
	 *
	 * @return mixed
	 */
	public function execute()
	{
		curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->_ch, CURLOPT_HTTPHEADER, [
			'x-api-key: '.$this->_api_key,
			'Content-Type: application/json',
			'Content-Length: '.strlen($this->payload)
		]);

		$result = curl_exec($this->_ch);
		$httpcode = curl_getinfo($this->_ch, CURLINFO_HTTP_CODE);
		if($httpcode != 200)
		{
			// Notify error
		}
		return json_decode($result);
	}

	/**
	 * Returns the stored payload as json or PHP array.
	 * Mainly for debugging.
	 *
	 * @param bool $json
	 * @return mixed
	 */
	public function getPayload($json = true)
	{
		return $json ? $this->payload : json_decode($this->payload);
	}
}

/**
 * Class ResolvedAddress
 */
class ResolvedAddress extends Address
{
	public $latitude;
	public $longitude;
	public $precision;
	public $locationTypes;

	/**
	 * Loads an address from a API call and returns
	 * a ResolvedAddress class object.
	 *
	 * @param \stdClass $obj
	 * @return ResolvedAddress
	 */
	public static function load(\stdClass $obj)
	{
		$res = new self(
			$obj->streetName,
			$obj->streetNumber,
			$obj->postalCode,
			$obj->city,
			$obj->country
		);

		$res->latitude = $obj->location->latitude;
		$res->longitude = $obj->location->longitude;
		$res->precision = $obj->precision;
		$res->locationTypes = $obj->locationTypes;

		return $res;
	}
}

/**
 * Class Window
 */
class Window
{
	public $start;
	public $end;
	public $product;
	public $price;
	public $currency;

	public function __construct($start, $end)
	{
		$this->start = $start;
		$this->end = $end;
	}

	/**
	 * Returns the delivery windows as a formatted array
	 * ready for json encoding.
	 *
	 * @return array
	 */
	public function getArray()
	{
		return [
			'start' => $this->start,
			'end' => $this->end,
		];
	}

	/**
	 * Loads a window from a API call and returns
	 * a Window class object.
	 *
	 * @param \stdClass $obj
	 * @return Window
	 */
	public static function load(\stdClass $obj)
	{
		$res = new self(
			$obj->start,
			$obj->end
		);

		$res->product = $obj->product;
		$res->price = $obj->price->fractionalDenomination;
		$res->currency = $obj->price->currency;

		return $res;
	}
}