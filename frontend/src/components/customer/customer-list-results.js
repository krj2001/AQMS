import React, { useState, useEffect } from 'react';
import { DataGrid } from '@mui/x-data-grid';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/DeleteOutlined';
import CustomerModal from './CustomerModalComponent';
import { CustomerListToolbar } from './customer-list-toolbar';
import { CustomerDeleteService, FetchCustomerService } from '../../services/LoginPageService';
import NotificationBar from '../notification/ServiceNotificationBar';
import ConfirmPassword from '../user/passwordConfirmComponent';
import { Card, CardContent, CardHeader } from '@mui/material';

export function CustomerListResults() {
  const columns = [
    {
      field: 'customerName',
      headerName: 'Customer Name',
      minwidth: 170,
      flex:1,
      align:'center',
      headerAlign: 'center'
    },
    {
      field: 'customerId',
      headerName: 'Company Code',
      minwidth: 130,
      flex:1,
      align:'center',
      headerAlign: 'center'
    },
    {
      field: 'email',
      headerName: 'Email Id',
      minwidth: 230,
      flex:1,
      align:'center',
      headerAlign: 'center'
    },
    {
      field: 'phoneNo',
      headerName: 'Phone',
      description: 'This column has a value getter and is not sortable.',
      sortable: false,
      minwidth: 120,
      flex:1,
      align:'center',
      headerAlign: 'center'
    },
    {
      field: 'address',
      headerName: 'Address',
      description: 'This column has a value getter and is not sortable.',
      sortable: false,
      minwidth: 160,
      flex:1,
      align:'center',
      headerAlign: 'center'
    },
    {
      field: 'actions',
      type: 'actions',
      headerName: 'Actions',
      minwidth: 100,
      flex:1,
      align:'center',
      cellClassName: 'actions',
      disableClickEventBubbling: true,
      getActions: (params) => [
        <EditData selectedRow={params.row} />, <DeleteData selectedRow={params.row} />,
      ],
    },
  ];

  const [open, setOpen] = useState(false);
  const [isAddButton, setIsAddButton] = useState(true);
  const [editCustomer, setEditCustomer] = useState([]);
  const [customerList, setCustomerList] = useState([]);
  const [isLoading, setGridLoading] = useState(true);
  const [id, setId] = useState('');
  const [password, setConfirmPassword] = useState('');
  const [btnReset, setBtnReset] = useState(false);
  const [refreshData, setRefreshData] = useState(false);
  const [openNotification, setNotification] = useState({
    status: false,
    type: 'error',
    message: '',
  });

  const handleSuccess = (dataObject) => {
    setGridLoading(false);
    setCustomerList(dataObject.data);
  };

  const handleException = (errorObject) => {
  };

  useEffect(() => {
    setGridLoading(true);
    FetchCustomerService(handleSuccess, handleException);
  }, [refreshData]);

  const passwordSubmit = async (e) => {
    e.preventDefault();
    CustomerDeleteService({ password, id }, passwordValidationSuccess, passwordValidationException);
    setBtnReset(false);
  };

  const passwordValidationSuccess = (dataObject) => {
    setNotification({
      status: true,
      type: 'success',
      message: dataObject.message,
    });
    setRefreshData((oldvalue) => !oldvalue);
  };

  const passwordValidationException = (errorObject, errorMessage) => {
    setNotification({
      status: true,
      type: 'error',
      message: errorMessage,
    });
  };

  function EditData(props) {
    return (
      <EditIcon
        style={{ cursor: 'pointer' }}
        onClick={(event) => {
          event.stopPropagation();
          setIsAddButton(false);
          setEditCustomer(props.selectedRow);
          setOpen(true);
        }}
      />
    );
  }

  function DeleteData(props) {
    return (
      <DeleteIcon
        onClick={() => {
          setId(props.selectedRow.id);
          setBtnReset(true);
        }}
        style={{ cursor: 'pointer' }}
      />
    );
  }

  const handleClose = () => {
    setNotification({
      status: false,
      type: '',
      message: '',
    });
  };

  return (
    <>
      <Card style={{boxShadow:'rgba(100, 100, 111, 0.2) 0px 7px 29px 0px', borderRadius:'12px'}}>
        <CardHeader 
          title={
            <CustomerListToolbar
              setIsAddButton={setIsAddButton}
              setEditCustomer={setEditCustomer}
              setOpen={setOpen}
            />
          }
        />
        <CardContent>
          <div className={'w-full h-96'}>
            <DataGrid
              rows={customerList}
              columns={columns}
              pageSize={5}
              loading={isLoading}
              rowsPerPageOptions={[5]}
              disableSelectionOnClick
              style={{border:'none'}}
            />
            <ConfirmPassword
              open={btnReset}
              passwordSubmit={passwordSubmit}
              setConfirmPassword={setConfirmPassword}
              setBtnReset={setBtnReset}
            />
            <CustomerModal
              isAddButton={isAddButton}
              customerData={editCustomer}
              open={open}
              setOpen={setOpen}
              setRefreshData={setRefreshData}
            />
            <NotificationBar
              handleClose={handleClose}
              notificationContent={openNotification.message}
              openNotification={openNotification.status}
              type={openNotification.type}
            />
          </div>
        </CardContent>
      </Card>
    </>
  );
}
