class Global {
    constructor() {
        $(document).on('domReady', () => {
            this.invalidInputHandler();
        });

        $(document).on('reloadInvalidInputHandler', this.invalidInputHandler);
    }

    invalidInputHandler() {
        $(".form-control").on("invalid", function () {
            // add .has-error class on invalid event
            $(this).addClass("has-error");
            // Listen to the change event on the inputs and remove the class if needed
            $(this).one("change paste keyup", function () {
                $(this).removeClass("has-error");
            });
        });
    }
}


export default new Global();