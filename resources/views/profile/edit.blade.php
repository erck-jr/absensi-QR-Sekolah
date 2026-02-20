<x-app-layout>
    <x-slot name="title">Profil Saya</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-material-card title="Profile Information" icon="person" color="blue">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </x-material-card>

            <x-material-card title="Update Password" icon="lock" color="orange">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </x-material-card>

            <x-material-card title="Delete Account" icon="warning" color="red">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </x-material-card>
        </div>
    </div>
</x-app-layout>
