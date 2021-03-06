<?php

namespace AwStudio\Fjord\Form\Controllers;

use AwStudio\Fjord\Support\Facades\FormLoader;
use AwStudio\Fjord\Form\FormFieldCollection;
use AwStudio\Fjord\Form\Database\FormField;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;

class FormController extends Controller
{
    public function update(Request $request, $id)
    {
        $formField = FormField::findOrFail($id);

        $formField->update($request->all());

        return $formField;
    }

    public function show(Request $request)
    {
        [$collection, $formName] = explode('.', str_replace('fjord.form.', '', Route::currentRouteName()));

        $this->setForm($collection, $formName);

        $eloquentFormFields = $this->getFormFields($collection, $formName);

        $this->form->setPreviewRoute(
            new FormFieldCollection($eloquentFormFields['data'])
        );
        return view('fjord::vue')->withComponent('crud-show')
            ->withModels([
                'model' => $eloquentFormFields
            ])
            ->withTitle($this->form->title)
            ->withProps([
                'formConfig' => $this->form->toArray(),
                'actions' => ['crud-show-preview'],
                'content' => ['crud-show-form']
            ]);
    }

    protected function getFormFields($collection, $form_name)
    {
        $formFields = [];

        foreach($this->form->form_fields as $key => $field) {

            $formFields[$key] = FormField::firstOrCreate(
                ['collection' => $collection, 'form_name' => $form_name, 'field_id' => $field->id],
                ['content' => $field->default ?? null]
            );

            if($field->type == 'block') {
                $formFields[$key]->withRelation($field->id);
            }

            if($field->type == 'relation') {
                $formFields[$key]->setFormRelation();
            }
            /*
            if($field['type'] == 'image') {
                $formFields[$key]->withRelation($field['id']);
            }
            */
        }

        return eloquentJs(collect($formFields), FormField::class);
    }

    protected function setForm($collection, $formName)
    {
        $formFieldInstance = new FormField();
        $formFieldInstance->collection = $collection;
        $formFieldInstance->form_name = $formName;

        $this->formPath = $formFieldInstance->form_fields_path;
        $this->form = FormLoader::load($this->formPath, FormField::class);
    }
}
