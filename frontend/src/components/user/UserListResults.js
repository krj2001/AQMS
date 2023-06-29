import React, { useState, useEffect } from 'react';
import { DataGrid } from '@mui/x-data-grid';
import { Edit as EditIcon, Delete as DeleteIcon } from '@mui/icons-material';
import {
  Tabs, Tab, Typography, Box, Card, CardHeader, CardContent,
} from '@mui/material';

import UserModal from './UserModalComponent';
import UserListToolbar from './UserListToolbar';
import { FetchUserService, UserDeleteService } from '../../services/LoginPageService';
import ConfirmPassword from './passwordConfirmComponent';
import NotificationBar from '../notification/ServiceNotificationBar';
import { useUserAccess } from '../../context/UserAccessProvider';
import UserLogForm from './UserLog/UserLogForm';

function TabPanel(props) {
  const {
    children, value, index, ...other
  } = props;
  return (
    <div
      role="tabpanel"
      hidden={value !== index}
      id={`simple-tabpanel-${index}`}
      aria-labelledby={`simple-tab-${index}`}
      {...other}
    >
      {value === index && (
        <Box sx={{ p: 3 }}>
          <Typography>{children}</Typography>
        </Box>
      )}
    </div>
  );
}

function a11yProps(index) {
  return {
    id: `simple-tab-${index}`,
    'aria-controls': `simple-tabpanel-${index}`,
  };
}

export default function UserListResults() {
  const [value, setValue] = React.useState(0);
  const handleChange = (event, newValue) => {
    setValue(newValue);
  };

  const columns = [
    {
      field: 'employeeId',
      headerName: 'Employee Id',
      width: 150,
      align:'center',
      headerAlign: 'center'
    },
    {
      field: 'name',
      headerName: 'Employee Name',
      width: 250,
      align:'center',
      headerAlign: 'center'
    },
    {
      field: 'email',
      headerName: 'Email Id',
      width: 270,
      align:'center',
      headerAlign: 'center'
    },
    {
      field: 'mobileno',
      headerName: 'Phone',
      description: 'This column has a value getter and is not sortable.',
      sortable: false,
      width: 180,
      align:'center',
      headerAlign: 'center'
    },
    {
      field: 'user_role',
      headerName: 'Employee Role',
      sortable: true,
      width: 200,
      align:'center',
      headerAlign: 'center'
    },
    {
      field: 'actions',
      type: 'actions',
      headerName: 'Actions',
      headerAlign: 'center',
      width: 180,
      align:'center',
      cellClassName: 'actions',
      getActions: (params) => [
        <EditData selectedRow={params.row} />,
        <DeleteData selectedRow={params.row} />,
      ],
    },
  ];

  const [open, setOpen] = useState(false);
  const [isAddButton, setIsAddButton] = useState(true);
  const [editUser, setEditUser] = useState([]);
  const [userList, setUserList] = useState([]);
  const [isLoading, setGridLoading] = useState(true);
  const [id, setId] = useState('');
  const [password, setConfirmPassword] = useState('');
  const [btnReset, setBtnReset] = useState(false);
  const [refreshData, setRefreshData] = useState(false);
  const moduleAccess = useUserAccess()('usermanagement');

  const [openNotification, setNotification] = useState({
    status: false,
    type: 'error',
    message: '',
  });
  const handleSuccess = (dataObject) => {
    setGridLoading(false);
    setUserList(dataObject?.data || []);
  };

  const handleException = () => {
  };

  useEffect(() => {
    FetchUserService(handleSuccess, handleException);
  }, [refreshData]);

  function EditData(props) {
    return (moduleAccess.edit
      && (
        <EditIcon
          onClick={(event) => {
            event.stopPropagation();
            setIsAddButton(false);
            setEditUser(props.selectedRow);
            setOpen(true);
          }}
          style={{ cursor: 'pointer' }}
        />
      ));
  }
  function DeleteData(props) {
    return moduleAccess.delete && (
      <DeleteIcon
        onClick={() => {
          setId(props.selectedRow.id);
          setBtnReset(true);
        }}
        style={{ cursor: 'pointer' }}
      />
    );
  }

  const passwordSubmit = async (e) => {
    e.preventDefault();
    UserDeleteService({ password, id }, passwordValidationSuccess, passwordValidationException);
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

  const handleClose = () => {
    setNotification({
      status: false,
      type: '',
      message: '',
    });
  };

  return (
    <Box sx={{ width: '100%', height: '85vh' }}>
    <Card className={'mt-[15px]'} style={{ boxShadow: 'rgba(100, 100, 111, 0.2) 0px 7px 29px 0px', borderRadius: '12px' }}>
      <CardHeader
        title={
          <Box className=' ml-5 mt-8'>
            <Tabs value={value} onChange={handleChange} aria-label="basic tabs example">
              <Tab label="Create User" sx={{ fontFamily: 'customfont', fontWeight: '600', letterSpacing: '1px' }} {...a11yProps(0)} />
              <Tab label="Log activity" sx={{ fontFamily: 'customfont', fontWeight: '600', letterSpacing: '1px' }} {...a11yProps(1)} />
            </Tabs>
          </Box>
        }
      />
      <CardContent className={'min-h-[550px]'} style={{ border: 'none', }}>
        <TabPanel value={value} index={0} className={'user-tab-wrapper'}>
          <div className={'h-[400px] w-full'}>
            <UserListToolbar
              setIsAddButton={setIsAddButton}
              setEditUser={setEditUser}
              setOpen={setOpen}
              editUser={editUser}
              userAccess={moduleAccess}
            />
            <DataGrid
              className={'py-5 px-0 '}
              sx={{ border: 'none', fontFamily: 'customfont' }}
              rows={userList}
              columns={columns}
              pageSize={3}
              loading={isLoading}
              rowsPerPageOptions={[3]}
              disableSelectionOnClick
            />
            <UserModal
              isAddButton={isAddButton}
              userData={editUser}
              open={open}
              setOpen={setOpen}
              setRefreshData={setRefreshData}
            />
            <ConfirmPassword
              open={btnReset}
              passwordSubmit={passwordSubmit}
              setConfirmPassword={setConfirmPassword}
              setBtnReset={setBtnReset}
            />
            <NotificationBar
              handleClose={handleClose}
              notificationContent={openNotification.message}
              openNotification={openNotification.status}
              type={openNotification.type}
            />
          </div>
        </TabPanel>
        <TabPanel value={value} index={1}>
          <UserLogForm />
        </TabPanel>
      </CardContent>
    </Card>
  </Box>
  );
}
