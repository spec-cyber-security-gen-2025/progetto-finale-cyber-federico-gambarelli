<x-layout>
    <h1 class="text-center display-1">Hi, {{Auth::user()->name}}</h1>

    <section class="container-fluid">
        <div class="row">
            <div class="col-6">
                <form action="{{route('profile.update')}}" method="POST" class="card p-5 shadow">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="name" class="form-label">Modifica Nome</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{Auth::user()->name}}">
                        @error('name')
                            <span class="text-danger">{{$message}}</span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Modifica Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{Auth::user()->email}}">
                        @error('email')
                            <span class="text-danger">{{$message}}</span>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-outline-secondary">Edit</button>
                </form>
            </div>
        </div>
    </section>
</x-layout>