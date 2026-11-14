@extends('errors::minimal')

@section('title', __('Too many requests'))
@section('code', '429')
@section('message', __('Too many requests'))
@section('description', __('Please wait a moment before trying again. You can also return to the home page.'))
