<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('customer.profile.update') }}" class="modal-content">
      @csrf
      @method('PUT')

      <div class="modal-header">
        <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <div class="mb-3">
          <label for="first_name" class="form-label">First Name</label>
          <input id="first_name" type="text" name="first_name" value="{{ old('first_name', auth('customer')->user()->first_name) }}" class="form-control" required>
          @error('first_name') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
          <label for="last_name" class="form-label">Last Name</label>
          <input id="last_name" type="text" name="last_name" value="{{ old('last_name', auth('customer')->user()->last_name) }}" class="form-control" required>
          @error('last_name') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input id="email" type="email" name="email" value="{{ old('email', auth('customer')->user()->email) }}" class="form-control" required>
          @error('email') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
          <label for="phone_number" class="form-label">Phone Number</label>
          <input id="phone_number" type="text" name="phone_number" value="{{ old('phone_number', auth('customer')->user()->phone_number) }}" class="form-control" required>
          @error('phone_number') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
          <label for="timezone" class="form-label">Timezone</label>
          <input id="timezone" type="text" name="timezone" value="{{ old('timezone', auth('customer')->user()->timezone) }}" class="form-control">
          @error('timezone') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>
