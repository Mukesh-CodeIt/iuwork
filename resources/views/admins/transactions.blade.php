@extends('admins.master')
@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Transactions</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ url('/admin/home') }}">Home</a></li>
            <li class="breadcrumb-item active">Transactions</li>
          </ol>
        </div>
      </div>
    </div><!-- /.container-fluid -->
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <!-- <div class="card-header">
            <h3 class="card-title">DataTable with default features</h3>
          </div> -->
          <!-- /.card-header -->
          <div class="card-body">
            <?php //dd($transactions); ?>
            <table id="example1" class="table table-bordered table-striped">
              <thead>
              <tr>
                <th>Transaction Date</th>
                <th>Sender</th>
                <th>Receiver</th>
                <th>Amount</th>
                <th>Balance</th>
                <th>Type</th>
                <th>Action</th>
              </tr>
              </thead>
              <tbody>
                @foreach($transactions as $data)
              <tr>
                <td><?php echo date('d M Y', strtotime($data->transaction_date)); ?></td>
                <td>{{ $data->user_from_name }}</td>
                <td>{{ $data->user_to_name }}</td>
                <td>{{ $data->amount }}</td>
                <td>{{ $data->balance }}</td>
                <td>{{ $data->transaction_type }}</td>
                <td>
                  <div>
                    @if($data->transaction_status == "completed")
                      <button class="btn btn-success btn-sm">Completed</button>
                    @elseif($data->transaction_status == "declined")
                      <button class="btn btn-danger btn-sm">Declined</button>
                    @else
                      <form action="/admin/approve_transaction" method="post" style="display: inline;">
                        @csrf
                        <input type="hidden" value="{{$data->transaction_id}}" name="transaction_id"/>
                        <button class="btn btn-sm btn-warning">Approve</button>
                      </form>
                      <form action="/admin/decline_transaction" method="post" style="display: inline;">
                        @csrf
                        <input type="hidden" value="{{$data->transaction_id}}" name="transaction_id"/>
                        <button class="btn btn-sm btn-danger">Decline</button>
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
                @endforeach
              </tbody>
              <tfoot>
              <tr>
                <th>Transaction Date</th>
                <th>Sender</th>
                <th>Receiver</th>
                <th>Amount</th>
                <th>Balance</th>
                <th>Type</th>
                <th>Action</th>
              </tr>
              </tfoot>
            </table>
          </div>
          <!-- /.card-body -->
        </div>
        <!-- /.card -->
      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->
  </section>
  <!-- /.content -->
</div>
@endsection
