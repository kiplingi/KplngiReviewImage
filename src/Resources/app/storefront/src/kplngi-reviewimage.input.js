import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';

export default class KplngiReviewImageInputName extends Plugin {
    init() {
        this._getInput();
        this._getAlert();
        this._getLabel();
        this._registerEvents();
    }

    _registerEvents() {
        this._input.addEventListener('change', this._inputChange.bind(this));
    }

    _getInput() {
        this._input = DomAccess.querySelector(this.el, '#reviewImageUpload');
    }

    _getAlert() {
        this._alert = DomAccess.querySelector(this.el, '.kplngi-reviewimage-input-validation');
    }

    _getLabel() {
        this._label = DomAccess.querySelector(this.el, 'label')
    }

    _inputChange(event) {
        if (!this._alert.classList.contains('d-none')) {
            this._alert.classList.toggle('d-none');
        }

        if (event.target.files[0].size > (1024 * 1024 * 2)) {
            event.target.value = null;
            this._label.innerHTML = '';
            this._alert.classList.toggle('d-none');
        } else {
            let fileName = event.target.value.split("\\").pop();
            this._label.classList.add("selected");
            this._label.innerHTML = fileName;
        }
    }
}
