document.addEventListener("animationstart", (e) => {
    if (e.animationName === "autofill") {
        e.target.classList.add("has-value");
    }
});
document.querySelectorAll(".input-label").forEach(input => {

    function checkValue() {
        if (input.value.trim() !== "") {
            input.classList.add("has-value");
        } else {
            input.classList.remove("has-value");
        }
    }

    input.addEventListener("input", checkValue);
    input.addEventListener("blur", checkValue);

    checkValue();

    setTimeout(checkValue, 1000);
});