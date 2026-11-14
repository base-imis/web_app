const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const template = fs.readFileSync(path.join(__dirname, '../../resources/views/fsm/cwis-setting/index.blade.php'), 'utf8');
const script = template.match(/<script>([\s\S]*?)<\/script>/)[1];

function setup({ errors = false, editable = true, loading = false } = {}) {
    const input = { readOnly: true };
    const select = { disabled: true };
    const updated = { hidden: false };
    const clicks = {};
    const edit = editable ? { hidden: false, addEventListener: (name, fn) => { clicks[name] = fn; } } : null;
    const save = editable ? { hidden: true } : null;
    const listeners = {};
    const form = {
        querySelector: selector => ({ '#editButton': edit, '#saveButton': save, '#cwis-settings-updated': updated })[selector],
        querySelectorAll: selector => selector === 'select' ? [select] : [input],
        addEventListener: (name, fn) => { listeners[name] = fn; },
    };
    const ready = {};
    const document = {
        readyState: loading ? 'loading' : 'complete',
        getElementById: id => id === 'editForm' ? form : null,
        addEventListener: (name, fn) => { ready[name] = fn; },
        querySelector: () => { throw new Error('Must not use unrelated page alerts to choose edit mode'); },
    };
    vm.runInNewContext(script.replace('@json($editingSettings)', String(errors && editable)), { document });
    if (loading) ready.DOMContentLoaded();
    return { input, select, edit, save, updated, clicks, listeners };
}

test('settings start read-only with Edit even if shared header has error alerts', () => {
    const state = setup();
    assert.equal(state.input.readOnly, true);
    assert.equal(state.select.disabled, true);
    assert.equal(state.edit.hidden, false);
    assert.equal(state.save.hidden, true);
    let blocked = false;
    state.listeners.submit({ preventDefault: () => { blocked = true; } });
    assert.equal(blocked, true);
});

test('Edit enables settings and shows Save without submitting', () => {
    const state = setup({ loading: true });
    state.clicks.click();
    assert.equal(state.input.readOnly, false);
    assert.equal(state.select.disabled, false);
    assert.equal(state.edit.hidden, true);
    assert.equal(state.save.hidden, false);
    assert.equal(state.updated.hidden, true);
    state.listeners.submit({ preventDefault: () => assert.fail('Editing should allow submission') });
});

test('actual form validation errors reopen authorized editing', () => {
    const state = setup({ errors: true });
    assert.equal(state.input.readOnly, false);
    assert.equal(state.select.disabled, false);
    assert.equal(state.save.hidden, false);
    assert.equal(state.edit.hidden, true);
});

test('users without Edit CWIS remain read-only even with validation errors', () => {
    const state = setup({ errors: true, editable: false });
    assert.equal(state.input.readOnly, true);
    assert.equal(state.select.disabled, true);
});

test('template defaults are server-rendered and legacy fields are removed', () => {
    assert.match(template, /'disabled' => !\$editingSettings/);
    assert.match(template, /'readonly' => !\$editingSettings/);
    assert.match(template, /type="button" id="editButton"/);
    assert.match(template, /id="saveButton"[^>]*@if\(!\$editingSettings\) hidden/);
    assert.doesNotMatch(template, /Legacy Parameters|data-legacy-setting|\.alert-danger/);
});
