import jquery from 'jquery';
import installSelect2 from 'select2';
import 'select2/dist/css/select2.css';
import '../css/consultancy-registration.css';

installSelect2(window, jquery);

const form = document.querySelector('#expert-registration');
const steps = [...form.querySelectorAll('[data-step]')];
const progress = form.querySelector('[data-progress]');
const markers = [...progress.querySelectorAll('[data-step-marker]')];
const back = form.querySelector('[data-back]');
const next = form.querySelector('[data-next]');
const submit = form.querySelector('[type=submit]');
const category = form.elements.service_category_id;
const expertise = form.querySelector('#expertise-services');
const expertiseSelect = jquery(expertise);
const expertiseHelp = form.querySelector('#expertise-help');
const expertiseRequired = form.querySelector('#expertise-required');
const selectionHelp = expertiseHelp.textContent;
const expertiseGroups = new Map([...expertise.querySelectorAll('optgroup')]
    .map(group => [group.dataset.category, [...group.children]]));

function updateExpertise(selected = []) {
    if (expertise.classList.contains('select2-hidden-accessible')) {
        expertiseSelect.select2('destroy');
    }

    const options = expertiseGroups.get(category.value) ?? [];
    expertise.replaceChildren(...options);
    options.forEach(option => { option.selected = selected.includes(option.value); });
    expertise.disabled = options.length === 0;
    expertise.required = options.length > 0;
    expertiseHelp.textContent = options.length ? selectionHelp :
        (category.value ? 'No areas are available for this category.' : 'Choose a consultancy category first.');
    expertiseRequired.hidden = true;
    expertiseSelect.select2({
        width: '100%',
        placeholder: expertiseHelp.textContent,
        dropdownCssClass: 'expertise-dropdown',
    });
}

updateExpertise(Array.from(expertise.selectedOptions, option => option.value));
category.addEventListener('change', () => updateExpertise());
expertiseSelect.on('change', () => { expertiseRequired.hidden = true; });
let current = 0;
const photo = form.querySelector('#profile-photo');
const preview = form.querySelector('[data-photo-preview]');
let previewUrl;

photo.addEventListener('change', () => {
    if (previewUrl) URL.revokeObjectURL(previewUrl);
    const file = photo.files[0];
    previewUrl = file ? URL.createObjectURL(file) : undefined;
    preview.hidden = !previewUrl;
    if (previewUrl) preview.src = previewUrl;
    else preview.removeAttribute('src');
});

function showStep(index, focus = true) {
    current = index;
    steps.forEach((step, position) => { step.hidden = position !== index; });
    markers.forEach((marker, position) => {
        marker.setAttribute('aria-current', position === index ? 'step' : 'false');
        marker.toggleAttribute('data-complete', position < index);
    });
    progress.querySelector('[data-progress-label]').textContent =
        `Step ${index + 1} of ${steps.length} · ${steps[index].dataset.step}`;
    back.hidden = index === 0;
    next.hidden = index === steps.length - 1;
    submit.hidden = !next.hidden;
    if (focus) steps[index].querySelector('h2').focus();
}

function validStep(step) {
    return [...step.querySelectorAll('input, select, textarea')].every(field => {
        if (field !== expertise || field.validity.valid) return field.reportValidity();

        expertiseRequired.hidden = false;
        expertiseSelect.select2('open');
        return false;
    });
}

function advance() {
    if (validStep(steps[current])) showStep(current + 1);
}

progress.hidden = false;
showStep(Math.max(0, steps.findIndex(step => step.dataset.invalid === 'yes')), false);
form.noValidate = true;
next.addEventListener('click', advance);
back.addEventListener('click', () => showStep(current - 1));
form.addEventListener('submit', event => {
    if (current < steps.length - 1) {
        event.preventDefault();
        advance();
        return;
    }

    const invalidStep = steps.findIndex(step => step.querySelector(':invalid'));
    if (invalidStep !== -1) {
        event.preventDefault();
        showStep(invalidStep, false);
        validStep(steps[invalidStep]);
    }
});

form.querySelectorAll('[data-repeater]').forEach(repeater => {
    const rows = repeater.querySelector('[data-rows]');
    const add = repeater.querySelector('[data-add]');
    const minimum = Number(repeater.dataset.min);
    let index = rows.children.length;

    function refresh() {
        rows.querySelectorAll('[data-remove]').forEach(button => {
            button.hidden = rows.children.length <= minimum;
        });
        add.disabled = rows.children.length >= 15;
    }

    add.hidden = false;
    refresh();
    add.addEventListener('click', () => {
        rows.insertAdjacentHTML('beforeend', repeater.querySelector('template').innerHTML.replaceAll('__index__', index++));
        refresh();
        rows.lastElementChild.querySelector('input').focus();
    });
    rows.addEventListener('click', event => {
        const button = event.target.closest('[data-remove]');
        if (!button || rows.children.length <= minimum) return;
        button.closest('fieldset').remove();
        refresh();
        add.focus();
    });
});
