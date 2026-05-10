@extends('layouts.siperlo')

@section('title', 'Tambah Lomba - SIPERLO')
@section('page_title', 'Tambah Lomba')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Kelola Lomba', 'route' => 'admin.competitions.index'],
        ['label' => 'Tambah Lomba'],
    ]" />
@endsection

@section('content')
<form method="POST" action="{{ route('admin.competitions.store') }}" enctype="multipart/form-data" class="siperlo-surface rounded-md p-5 lg:p-6">
    @include('admin.competitions._form')
</form>
@endsection
