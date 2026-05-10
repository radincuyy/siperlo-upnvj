@extends('layouts.siperlo')

@section('title', 'Edit Lomba - SIPERLO')
@section('page_title', 'Edit Lomba')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Kelola Lomba', 'route' => 'admin.competitions.index'],
        ['label' => $competition->title, 'url' => route('admin.competitions.show', $competition)],
        ['label' => 'Edit'],
    ]" />
@endsection

@section('content')
<form method="POST" action="{{ route('admin.competitions.update', $competition) }}" enctype="multipart/form-data" class="siperlo-surface rounded-md p-5 lg:p-6">
    @method('PUT')
    @include('admin.competitions._form')
</form>
@endsection
