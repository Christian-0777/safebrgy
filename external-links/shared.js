document.addEventListener("DOMContentLoaded", () => {
	const toggle = document.querySelector(".menu-toggle");
	const navigation = document.querySelector(".site-nav");

	if (toggle && navigation) {
		toggle.addEventListener("click", () => {
			const isOpen = navigation.classList.toggle("open");
			toggle.setAttribute("aria-expanded", String(isOpen));
		});
	}

	document.querySelectorAll("[data-current-year]").forEach((element) => {
		element.textContent = String(new Date().getFullYear());
	});
});
