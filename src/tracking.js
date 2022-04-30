import "./scss/tracking.scss";

(function () {
	const form = document.getElementById("ceva-track");
	form.addEventListener("submit", function logSubmit(event) {
		event.preventDefault();

		document.getElementById("tracking-results").innerHTML = "Loading...";
		document.getElementById("booking-submit").disabled = true;

		const bookingNumber = document.getElementById("booking-number").value;

		fetch(
			`${formidable_booking_track.ajaxurl}?action=ceva_track&booking_number=${bookingNumber}`
		)
			.then((response) => response.json())
			.then((response) => {
				if (response.success) {
					document.getElementById("tracking-results").innerHTML =
						response.data.html;
					document.getElementById("booking-submit").disabled = false;
				}
			});
	});
})();
