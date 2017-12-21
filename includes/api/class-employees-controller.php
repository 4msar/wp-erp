<?php

namespace WeDevs\ERP\API;

use WeDevs\ERP\HRM\Employee;
use WeDevs\ERP\HRM\Models\Department;
use WeDevs\ERP\HRM\Models\Dependents;
use WeDevs\ERP\HRM\Models\Designation;
use WeDevs\ERP\HRM\Models\Education;
use WeDevs\ERP\HRM\Models\Performance;
use WeDevs\ERP\HRM\Models\Work_Experience;
use WeDevs\ERP\HRM\Models\Employee_Note;
use WP_Error;
use WP_REST_Response;
use WP_REST_Server;

class Employees_Controller extends REST_Controller {
    /**
     * Endpoint namespace.
     *
     * @var string
     */
    protected $namespace = 'erp/v1';

    /**
     * Route base.
     *
     * @var string
     */
    protected $rest_base = 'hrm/employees';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_employees' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_employee' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_create_employee' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/bulk', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_employees' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_create_employee' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_employee' ],
                'args'                => [
                    'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_employee' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_edit_employee' );
                },
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_employee' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_delete_employee' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/experiences', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_experiences' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_experience' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_edit_employee' );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/experiences' . '/(?P<exp_id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_experience' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_experience' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_edit_employee' );
                },
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_experience' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_edit_employee' );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/educations', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_educations' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_education' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_edit_employee' );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/educations' . '/(?P<edu_id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_education' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_education' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_edit_employee' );
                },
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_education' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_edit_employee' );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/dependents', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_dependents' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_dependent' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_edit_employee' );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/dependents' . '/(?P<dep_id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_dependent' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_dependent' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_edit_employee' );
                },
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_dependent' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_edit_employee' );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/policies', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_policies' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/leaves', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_leaves' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_leave' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/notes', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_notes' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_note' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_edit_employee' );
                },
            ],
        ] );
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/notes' . '/(?P<note_id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_note' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_edit_employee' );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/performances', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_performances' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_performance' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_edit_employee' );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/performances' . '/(?P<performance_id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_performance' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_edit_employee' );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/histories', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_histories' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_history' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_edit_employee' );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/histories' . '/(?P<history_id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_history' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_edit_employee' );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/roles', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_roles' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( erp_hr_get_manager_role() );
                },
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_role' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( erp_hr_get_manager_role() );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/events', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_events' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ]
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/terminate', [
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'create_terminate' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( erp_hr_get_manager_role() );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/announcements', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_announcements' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_status' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
        ] );

    }

    /**
     * Get a collection of employees
     *
     * @param $request \WP_REST_Request
     *
     * @return WP_REST_Response
     */
    public function get_employees( $request ) {
        $args = [
            'number'      => $request['per_page'],
            'offset'      => ( $request['per_page'] * ( $request['page'] - 1 ) ),
            'status'      => ( $request['status'] ) ? $request['status'] : 'active',
            'department'  => ( $request['department'] ) ? $request['department'] : '-1',
            'designation' => ( $request['designation'] ) ? $request['designation'] : '-1',
            'location'    => ( $request['location'] ) ? $request['location'] : '-1',
            // 's'           => ( $request['s'] ) ? $request['s'] : '',
        ];

        $items = erp_hr_get_employees( $args );

        $args['count'] = true;
        $total_items   = erp_hr_get_employees( $args );

        $formatted_items = [];
        foreach ( $items as $item ) {
            $additional_fields = [];
            $data              = $this->prepare_item_for_response( $item, $request, $additional_fields );
            $formatted_items[] = $this->prepare_response_for_collection( $data );
        }
        $total_pages = ceil( $total_items / $request['per_page'] );

        $response = rest_ensure_response( $formatted_items );
        $response = $this->format_collection_response( $response, $request, (int) $total_items );

        return $response;
    }

    /**
     * Get a specific employee
     *
     * @param \WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_employee( $request ) {
        $id   = (int) $request['id'];
        $item = new Employee( $id );

        if ( empty( $id ) || empty( $item->id ) ) {
            return new WP_Error( 'rest_employee_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $item     = $this->prepare_item_for_response( $item, $request );
        $response = rest_ensure_response( $item );

        return $response;
    }

    /**
     * Create an employee
     *
     * @param \WP_REST_Request $request
     *
     * @return WP_Error|\WP_REST_Request
     */
    public function create_employee( $request ) {
        $item = $this->prepare_item_for_database( $request );

        $id       = erp_hr_employee_create( $item );
        $employee = new Employee( $id );

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_item_for_response( $employee, $request );
        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $id ) ) );

        return $response;
    }

    /**
     * Create employees
     *
     * @param \WP_REST_Request $request
     *
     * @return WP_Error|\WP_REST_Request
     */
    public function create_employees( $request ) {
        $employees = json_decode( $request->get_body(), true );

        foreach ( $employees as $employee ) {
            $item = $this->prepare_item_for_database( $employee );
            $id   = erp_hr_employee_create( $item );

            if ( is_wp_error( $id ) ) {
                return $id;
            }
        }

        return new WP_REST_Response( true, 201 );
    }

    /**
     * Update an employee
     *
     * @param \WP_REST_Request $request
     *
     * @return WP_Error|\WP_REST_Request
     */
    public function update_employee( $request ) {
        $id = (int) $request['id'];

        $employee = new Employee( $id );
        if ( ! $employee ) {
            return new WP_Error( 'rest_employee_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 400 ] );
        }

        $item = $this->prepare_item_for_database( $request );
        $id   = erp_hr_employee_create( $item );

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_item_for_response( $employee, $request );
        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $id ) ) );

        return $response;
    }

    /**
     * Delete an employee
     *
     * @param $request
     *
     * @return \WP_REST_Response
     */
    public function delete_employee( $request ) {
        $id = (int) $request['id'];

        erp_employee_delete( $id );
        $response = rest_ensure_response( true );

        return new WP_REST_Response( $response, 204 );
    }

    /**
     * Prepare a single item for create or update
     *
     * @param \WP_REST_Request $request Request object.
     *
     * @return array $prepared_item
     */
    protected function prepare_item_for_database( $request ) {
        $prepared_item = [];

        // required arguments.
        if ( isset( $request['first_name'] ) ) {
            $prepared_item['personal']['first_name'] = $request['first_name'];
        }

        if ( isset( $request['last_name'] ) ) {
            $prepared_item['personal']['last_name'] = $request['last_name'];
        }

        if ( isset( $request['email'] ) ) {
            $prepared_item['user_email'] = $request['email'];
        }

        // optional arguments.
        if ( isset( $request['id'] ) ) {
            $prepared_item['id'] = absint( $request['id'] );
        }

        if ( isset( $request['middle_name'] ) ) {
            $prepared_item['personal']['middle_name'] = $request['middle_name'];
        }

        if ( isset( $request['designation'] ) ) {
            $prepared_item['work']['designation'] = $request['designation'];
        }

        if ( isset( $request['department'] ) ) {
            $prepared_item['work']['department'] = $request['department'];
        }

        if ( isset( $request['reporting_to'] ) ) {
            $prepared_item['work']['reporting_to'] = $request['reporting_to'];
        }

        if ( isset( $request['location'] ) ) {
            $prepared_item['work']['location'] = $request['location'];
        }

        if ( isset( $request['hiring_source'] ) ) {
            $prepared_item['work']['hiring_source'] = $request['hiring_source'];
        }

        if ( isset( $request['hiring_date'] ) ) {
            $prepared_item['work']['hiring_date'] = $request['hiring_date'];
        }

        if ( isset( $request['date_of_birth'] ) ) {
            $prepared_item['work']['date_of_birth'] = $request['date_of_birth'];
        }

        if ( isset( $request['pay_rate'] ) ) {
            $prepared_item['work']['pay_rate'] = $request['pay_rate'];
        }

        if ( isset( $request['pay_type'] ) ) {
            $prepared_item['work']['pay_type'] = $request['pay_type'];
        }

        if ( isset( $request['type'] ) ) {
            $prepared_item['work']['type'] = $request['type'];
        }

        if ( isset( $request['status'] ) ) {
            $prepared_item['work']['status'] = $request['status'];
        }

        if ( isset( $request['other_email'] ) ) {
            $prepared_item['personal']['other_email'] = $request['other_email'];
        }

        if ( isset( $request['phone'] ) ) {
            $prepared_item['personal']['phone'] = $request['phone'];
        }

        if ( isset( $request['work_phone'] ) ) {
            $prepared_item['personal']['work_phone'] = $request['work_phone'];
        }

        if ( isset( $request['mobile'] ) ) {
            $prepared_item['personal']['mobile'] = $request['mobile'];
        }

        if ( isset( $request['address'] ) ) {
            $prepared_item['personal']['address'] = $request['address'];
        }

        if ( isset( $request['gender'] ) ) {
            $prepared_item['personal']['gender'] = $request['gender'];
        }

        if ( isset( $request['marital_status'] ) ) {
            $prepared_item['personal']['marital_status'] = $request['marital_status'];
        }

        if ( isset( $request['nationality'] ) ) {
            $prepared_item['personal']['nationality'] = $request['nationality'];
        }

        if ( isset( $request['driving_license'] ) ) {
            $prepared_item['personal']['driving_license'] = $request['driving_license'];
        }

        if ( isset( $request['hobbies'] ) ) {
            $prepared_item['personal']['hobbies'] = $request['hobbies'];
        }

        if ( isset( $request['user_url'] ) ) {
            $prepared_item['personal']['user_url'] = $request['user_url'];
        }

        if ( isset( $request['description'] ) ) {
            $prepared_item['personal']['description'] = $request['description'];
        }

        if ( isset( $request['street_1'] ) ) {
            $prepared_item['personal']['street_1'] = $request['street_1'];
        }

        if ( isset( $request['street_2'] ) ) {
            $prepared_item['personal']['street_2'] = $request['street_2'];
        }

        if ( isset( $request['city'] ) ) {
            $prepared_item['personal']['city'] = $request['city'];
        }

        if ( isset( $request['country'] ) ) {
            $prepared_item['personal']['country'] = $request['country'];
        }

        if ( isset( $request['state'] ) ) {
            $prepared_item['personal']['state'] = $request['state'];
        }

        if ( isset( $request['postal_code'] ) ) {
            $prepared_item['personal']['postal_code'] = $request['postal_code'];
        }

        return $prepared_item;
    }

    /**
     * Prepare a single user output for response
     *
     * @param object           $item
     * @param \WP_REST_Request $request Request object.
     * @param array            $additional_fields (optional)
     *
     * @return WP_REST_Response $response Response data.
     */
    public function prepare_item_for_response( $item, $request, $additional_fields = [] ) {
        $data = [
            'id'              => (int) $item->id,
            'employee_id'     => (int) $item->employee_id,
            'first_name'      => $item->first_name,
            'middle_name'     => $item->middle_name,
            'last_name'       => $item->last_name,
            'email'           => $item->user_email,
            'location'        => $item->location,
            'hiring_source'   => $item->hiring_source,
            'hiring_date'     => $item->hiring_date,
            'date_of_birth'   => $item->date_of_birth,
            'pay_rate'        => (int) $item->pay_rate,
            'pay_type'        => $item->pay_type,
            'type'            => $item->type,
            'status'          => $item->status,
            'other_email'     => $item->other_email,
            'phone'           => $item->phone,
            'work_phone'      => $item->work_phone,
            'mobile'          => $item->mobile,
            'address'         => $item->address,
            'gender'          => $item->gender,
            'marital_status'  => $item->marital_status,
            'nationality'     => $item->nationality,
            'driving_license' => $item->driving_license,
            'hobbies'         => $item->hobbies,
            'user_url'        => $item->user_url,
            'description'     => $item->description,
            'street_1'        => $item->street_1,
            'street_2'        => $item->street_2,
            'city'            => $item->city,
            'country'         => $item->country,
            'state'           => $item->state,
            'postal_code'     => $item->postal_code,
        ];

        if ( isset( $request['include'] ) ) {
            $include_params = explode( ',', str_replace( ' ', '', $request['include'] ) );

            if ( in_array( 'department', $include_params ) ) {
                $departments_controller = new Departments_Controller();

                $department_id      = (int) $item->department;
                $data['department'] = null;

                if ( $department_id ) {
                    $department         = $departments_controller->get_department( [ 'id' => $department_id ] );
                    $data['department'] = ! is_wp_error( $department ) ? $department->get_data() : null;
                }
            }

            if ( in_array( 'designation', $include_params ) ) {
                $designations_controller = new Designations_Controller();

                $designation_id      = (int) $item->designation;
                $data['designation'] = null;

                if ( $designation_id ) {
                    $designation         = $designations_controller->get_designation( [ 'id' => $designation_id ] );
                    $data['designation'] = ! is_wp_error( $designation ) ? $designation->get_data() : null;
                }
            }

            if ( in_array( 'reporting_to', $include_params ) ) {
                $reporting_to_id      = (int) $item->reporting_to;
                $data['reporting_to'] = null;

                if ( $reporting_to_id ) {
                    $reporting_to         = $this->get_employee( [ 'id' => $reporting_to_id ] );
                    $data['reporting_to'] = ! is_wp_error( $reporting_to ) ? $reporting_to->get_data() : null;
                }
            }

            if ( in_array( 'avatar', $include_params ) ) {
                $employee_user      = new \WeDevs\ERP\HRM\Employee( intval( $item->id ) );
                $data['avatar_url'] = $employee_user->get_avatar_url( 32 );
            }

            if ( in_array( 'roles', $include_params ) ) {
                $employee_user = new \WeDevs\ERP\HRM\Employee( intval( $item->id ) );
                $data['roles'] = $employee_user->get_roles();
            }
        }

        $data = array_merge( $data, $additional_fields );

        // Wrap the data in a response object
        $response = rest_ensure_response( $data );

        $response = $this->add_links( $response, $item );

        return $response;
    }

    /**
     * Get a collection of employee's experiences
     *
     * @param \WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_experiences( $request ) {
        $employee_id = (int) $request['id'];
        $employee    = new Employee( $employee_id );
        $items       = $employee->get_experiences();

        $formatted_items = [];
        foreach ( $items as $item ) {
            $data              = $this->prepare_experience_for_response( $item, $request );
            $formatted_items[] = $this->prepare_response_for_collection( $data );
        }
        $total_items = count( $formatted_items );
        $response    = rest_ensure_response( $formatted_items );
        $response    = $this->format_collection_response( $response, $request, $total_items );

        return $response;
    }

    /**
     * Get a specific experience of an employee
     *
     * @param \WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_experience( $request ) {
        $exp_id      = (int) $request['exp_id'];
        $employee_id = (int) $request['id'];

        $experience = Work_Experience::where( [ 'id' => $exp_id, 'employee_id' => $employee_id ] )->first();

        if ( ! $experience ) {
            return new WP_Error( 'rest_invalid_experience', __( 'Invalid experience id.' ), array( 'status' => 404 ) );
        }

        $response = $this->prepare_experience_for_response( $experience, $request );

        return $response;
    }

    /**
     * Create an experience
     *
     * @param \WP_REST_Request $request
     *
     * @return WP_Error|\WP_REST_Request
     */
    public function create_experience( $request ) {
        $request['employee_id'] = $request['id'];
        unset( $request['id'] );

        if ( ! isset( $request['company_name'] ) ) {
            return new WP_Error( 'rest_experience_required_fields', __( 'Required company_name.' ), array( 'status' => 400 ) );
        }

        if ( ! isset( $request['job_title'] ) ) {
            return new WP_Error( 'rest_experience_required_fields', __( 'Required job_title.' ), array( 'status' => 400 ) );
        }

        if ( ! isset( $request['from'] ) ) {
            return new WP_Error( 'rest_experience_required_fields', __( 'Required from.' ), array( 'status' => 400 ) );
        }

        if ( ! isset( $request['to'] ) ) {
            return new WP_Error( 'rest_experience_required_fields', __( 'Required to.' ), array( 'status' => 400 ) );
        }

        $item       = $this->prepare_experience_for_database( $request );
        $experience = Work_Experience::create( $item );

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_experience_for_response( $experience, $request );

        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $request['id'] ) ) );

        return $response;
    }

    /**
     * Update an experience
     *
     * @param \WP_REST_Request $request
     *
     * @return WP_Error|\WP_REST_Request
     */
    public function update_experience( $request ) {

        $item       = $this->prepare_experience_for_database( $request );
        $experience = Work_Experience::find( $request['id'] );
        if ( $experience ) {
            $is_updated = $experience->update( $item );
        }

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_experience_for_response( $experience, $request );

        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $request['id'] ) ) );

        return $response;
    }

    /**
     * Delete an experience
     *
     * @param \WP_REST_Request $request
     *
     * @return WP_Error|\WP_REST_Request
     */
    public function delete_experience( $request ) {
        $id = (int) $request['exp_id'];

        Work_Experience::find( $id )->delete();

        return new WP_REST_Response( true, 204 );
    }

    /**
     * Prepare a single experience for create or update
     *
     * @param \WP_REST_Request $request Request object.
     *
     * @return array $prepared_item
     */
    protected function prepare_experience_for_database( $request ) {
        $prepared_item = [];

        // required arguments.
        if ( isset( $request['company_name'] ) ) {
            $prepared_item['company_name'] = $request['company_name'];
        }

        if ( isset( $request['job_title'] ) ) {
            $prepared_item['job_title'] = $request['job_title'];
        }

        if ( isset( $request['description'] ) ) {
            $prepared_item['description'] = $request['description'];
        }

        if ( isset( $request['from'] ) ) {
            $prepared_item['from'] = $request['from'];
        }

        if ( isset( $request['to'] ) ) {
            $prepared_item['to'] = $request['to'];
        }

        if ( isset( $request['employee_id'] ) ) {
            $prepared_item['employee_id'] = absint( $request['employee_id'] );
        }

        // optional arguments.
        if ( isset( $request['id'] ) ) {
            $prepared_item['id'] = absint( $request['id'] );
        }

        return $prepared_item;
    }

    /**
     * Prepare a single experience output for response
     *
     * @param object           $item
     * @param \WP_REST_Request $request Request object.
     * @param array            $additional_fields (optional)
     *
     * @return WP_REST_Response $response Response data.
     */
    public function prepare_experience_for_response( $item, $request, $additional_fields = [] ) {
        $data = [
            'id'           => (int) $item->id,
            'company_name' => $item->company_name,
            'job_title'    => $item->job_title,
            'description'  => $item->description,
            'from'         => $item->from,
            'to'           => $item->to,
        ];

        $data = array_merge( $data, $additional_fields );

        // Wrap the data in a response object
        $response = rest_ensure_response( $data );

        return $response;
    }

    /**
     * Get a collection of employee's educations
     *
     * @param \WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_educations( $request ) {
        $employee_id = (int) $request['id'];
        $employee    = new Employee( $employee_id );
        $items       = $employee->get_educations();

        $formatted_items = [];
        foreach ( $items as $item ) {
            $data              = $this->prepare_education_for_response( $item, $request );
            $formatted_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formatted_items );
        $response = $this->format_collection_response( $response, $request, count( $items ) );

        return $response;
    }

    /**
     * Get a specific education of an employee
     *
     * @param \WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_education( $request ) {
        $edu_id      = (int) $request['edu_id'];
        $employee_id = (int) $request['id'];

        $education = Education::where( [ 'id' => $edu_id, 'employee_id' => $employee_id ] )->first();

        if ( ! $education ) {
            return new WP_Error( 'rest_invalid_education', __( 'Invalid education id.' ), array( 'status' => 404 ) );
        }

        $response = $this->prepare_education_for_response( $education, $request );

        return $response;
    }

    /**
     * Create an education
     *
     * @param \WP_REST_Request $request
     *
     * @return WP_Error|\WP_REST_Request
     */
    public function create_education( $request ) {
        $request['employee_id'] = $request['id'];
        unset( $request['id'] );

        if ( ! isset( $request['school'] ) ) {
            return new WP_Error( 'rest_experience_required_fields', __( 'Required school.' ), array( 'status' => 400 ) );
        }

        if ( ! isset( $request['degree'] ) ) {
            return new WP_Error( 'rest_experience_required_fields', __( 'Required degree.' ), array( 'status' => 400 ) );
        }

        if ( ! isset( $request['field'] ) ) {
            return new WP_Error( 'rest_experience_required_fields', __( 'Required field.' ), array( 'status' => 400 ) );
        }

        $item      = $this->prepare_education_for_database( $request );
        $education = Education::create( $item );

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_education_for_response( $education, $request );

        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $request['id'] ) ) );

        return $response;
    }

    /**
     * Update an education
     *
     * @param \WP_REST_Request $request
     *
     * @return WP_Error|\WP_REST_Request
     */
    public function update_education( $request ) {
        $request['employee_id'] = $request['id'];
        unset( $request['id'] );
        $request['id'] = (int) $request['edu_id'];

        $item      = $this->prepare_education_for_database( $request );
        $education = Education::find( $request['id'] );
        if ( $education ) {
            $is_updated = $education->update( $item );
        }

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_education_for_response( $education, $request );

        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $id ) ) );

        return $response;
    }

    /**
     * Delete an education
     *
     * @param \WP_REST_Request $request
     *
     * @return WP_Error|\WP_REST_Request
     */
    public function delete_education( $request ) {
        $id = (int) $request['edu_id'];

        Education::find( $id )->delete();

        return new WP_REST_Response( true, 204 );
    }

    /**
     * Prepare a single education for create or update
     *
     * @param \WP_REST_Request $request Request object.
     *
     * @return array $prepared_item
     */
    protected function prepare_education_for_database( $request ) {
        $prepared_item = [];

        // required arguments.
        if ( isset( $request['school'] ) ) {
            $prepared_item['school'] = $request['school'];
        }

        if ( isset( $request['degree'] ) ) {
            $prepared_item['degree'] = $request['degree'];
        }

        if ( isset( $request['field'] ) ) {
            $prepared_item['field'] = $request['field'];
        }

        if ( isset( $request['finished'] ) ) {
            $prepared_item['finished'] = $request['finished'];
        }

        if ( isset( $request['notes'] ) ) {
            $prepared_item['notes'] = $request['notes'];
        }

        if ( isset( $request['interest'] ) ) {
            $prepared_item['interest'] = $request['interest'];
        }

        if ( isset( $request['employee_id'] ) ) {
            $prepared_item['employee_id'] = absint( $request['employee_id'] );
        }

        // optional arguments.
        if ( isset( $request['id'] ) ) {
            $prepared_item['id'] = absint( $request['id'] );
        }

        return $prepared_item;
    }

    /**
     * Prepare a single education output for response
     *
     * @param object           $item
     * @param \WP_REST_Request $request Request object.
     * @param array            $additional_fields (optional)
     *
     * @return WP_REST_Response $response Response data.
     */
    public function prepare_education_for_response( $item, $request, $additional_fields = [] ) {
        $data = [
            'id'       => (int) $item->id,
            'school'   => $item->school,
            'degree'   => $item->degree,
            'field'    => $item->field,
            'finished' => $item->finished,
            'notes'    => $item->notes,
            'interest' => $item->interest,
        ];

        $data = array_merge( $data, $additional_fields );

        // Wrap the data in a response object
        $response = rest_ensure_response( $data );

        return $response;
    }

    /**
     * Get a collection of employee's dependents
     *
     * @param \WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_dependents( $request ) {
        $employee_id = (int) $request['id'];
        $employee    = new Employee( $employee_id );
        $items       = $employee->get_dependents();

        $formatted_items = [];
        foreach ( $items as $item ) {
            $data              = $this->prepare_dependent_for_response( $item, $request );
            $formatted_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formatted_items );
        $response = $this->format_collection_response( $response, $request, count( $items ) );

        return $response;
    }

    /**
     * Get a specific dependent of an employee
     *
     * @param \WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_dependent( $request ) {
        $dep_id      = (int) $request['dep_id'];
        $employee_id = (int) $request['id'];

        $dependent = Dependents::where( [ 'id' => $dep_id, 'employee_id' => $employee_id ] )->first();

        if ( ! $dependent ) {
            return new WP_Error( 'rest_invalid_dependent', __( 'Invalid dependent id.' ), array( 'status' => 404 ) );
        }

        $dependents = $this->prepare_dependent_for_response( $dependent, $request );
        $response   = rest_ensure_response( $dependents );

        return $response;
    }

    /**
     * Create a dependent
     *
     * @param \WP_REST_Request $request
     *
     * @return WP_Error|\WP_REST_Request
     */
    public function create_dependent( $request ) {
        $request['employee_id'] = $request['id'];
        unset( $request['id'] );

        if ( ! isset( $request['name'] ) ) {
            return new WP_Error( 'rest_experience_required_fields', __( 'Required name.' ), array( 'status' => 400 ) );
        }

        if ( ! isset( $request['relation'] ) ) {
            return new WP_Error( 'rest_experience_required_fields', __( 'Required relation.' ), array( 'status' => 400 ) );
        }

        $item      = $this->prepare_dependent_for_database( $request );
        $dependent = Dependents::create( $item );

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_dependent_for_response( $dependent, $request );

        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $request['id'] ) ) );

        return $response;
    }

    /**
     * Update a dependent
     *
     * @param \WP_REST_Request $request
     *
     * @return WP_Error|\WP_REST_Request
     */
    public function update_dependent( $request ) {
        $request['employee_id'] = $request['id'];
        unset( $request['id'] );
        $request['id'] = (int) $request['dep_id'];

        $item      = $this->prepare_dependent_for_database( $request );
        $dependent = Dependents::find( $request['id'] );
        if ( $dependent ) {
            $is_updated = $dependent->update( $item );
        }

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_dependent_for_response( $dependent, $request );

        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $id ) ) );

        return $response;
    }

    /**
     * Delete a dependent
     *
     * @param \WP_REST_Request $request
     *
     * @return WP_Error|\WP_REST_Request
     */
    public function delete_dependent( $request ) {
        $id = (int) $request['dep_id'];

        Dependents::find( $id )->delete();

        return new WP_REST_Response( true, 204 );
    }

    /**
     * Get Available leaves policies of a single employee
     *
     * @since 1.2.9
     *
     * @param \WP_REST_Request $request
     *
     * @return array
     */
    public function get_policies( $request ) {
        $id       = (int) $request['id'];
        $employee = new Employee( $id );
        if ( ! $employee ) {
            return new WP_Error( 'rest_invalid_employee_id', __( 'Invalid Employee id.' ), array( 'status' => 404 ) );
        }
        $found_policies   = array();
        $policies         = erp_hr_leave_get_policies();
        $entitlements     = erp_hr_leave_get_entitlements( array( 'employee_id' => $employee->id ) );
        $entitlements_pol = wp_list_pluck( $entitlements, 'policy_id' );
        $balance          = erp_hr_leave_get_balance( $employee->id );
        if ( $policies ) {
            foreach ( $policies as $num => $policy ) {
                $key       = array_search( $policy->id, $entitlements_pol );
                $en        = false;
                $name      = esc_html( $policy->name );
                $current   = 0;
                $scheduled = 0;
                $available = $policy->value;

                if( !$entitlements ) continue;

                if ( array_key_exists( $policy->id, $balance ) ) {
                    $current   = $balance[ $policy->id ]['entitlement'];
                    $scheduled = $balance[ $policy->id ]['scheduled'];
                    $available = $balance[ $policy->id ]['entitlement'] - $balance[ $policy->id ]['total'];
                }

                if ( false !== $key ) {
                    $en = $entitlements[ $key ];

                }

                $found_policies[] = array(
                    'id'          => $policy->id,
                    'policy'      => $policy->name,
                    'total'       => $en ? sprintf( __( '%d days', 'erp' ), number_format_i18n( $en->days ) ) : 0,
                    'scheduled'   => $en ? sprintf( __( '%d days', 'erp' ), number_format_i18n( $scheduled ) ) : 0,
                    'available'   => sprintf( __( '%d days', 'erp' ), number_format_i18n( $available ) ),
                    'preiod_from' => erp_format_date( $en->from_date ),
                    'preiod_to'   => erp_format_date( $en->to_date ),
                );
            }
        }
        $response = rest_ensure_response( $found_policies );
        $response = $this->format_collection_response( $response, $request, count( $found_policies ) );

        return $response;
    }

    /**
     * Get all leaves of a single employee
     *
     * @since 1.2.9
     *
     * @param \WP_REST_Request $request
     *
     * @return array|WP_Error|object
     */
    public function get_leaves( \WP_REST_Request $request ) {
        $id       = (int) $request['id'];
        $employee = new Employee( $id );
        if ( ! $employee ) {
            return new WP_Error( 'rest_invalid_employee_id', __( 'Invalid Employee id.' ), array( 'status' => 404 ) );
        }

        $args = array(
            'user_id' => $id,
            'orderby' => 'req.start_date',
        );

        $items = erp_hr_get_leave_requests( $args );

        $formatted_items = [];
        foreach ( $items as $item ) {
            $data              = $this->prepare_leave_for_response( $item, $request );
            $formatted_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formatted_items );
        $response = $this->format_collection_response( $response, $request, count( $items ) );

        return $response;
    }

    /**
     * Create leave request
     *
     * @since 1.2.9
     *
     * @param \WP_REST_Request $request
     *
     * @return array|WP_Error|object
     */
    public function create_leave( $request ) {
        $id       = (int) $request['id'];
        $employee = new Employee( $id );

        if ( ! $employee ) {
            return new WP_Error( 'rest_invalid_employee_id', __( 'Invalid Employee id.' ), array( 'status' => 404 ) );
        }

        if ( empty( $request['policy_id'] ) ) {
            return new WP_Error( 'rest_invalid_policy_id', __( 'Invalid Policy id.' ), array( 'status' => 404 ) );
        }

        if ( empty( $request['start_date'] ) ) {
            return new WP_Error( 'rest_invalid_start_date', __( 'Invalid Leave Start Date.' ), array( 'status' => 404 ) );
        }

        if ( empty( $request['end_date'] ) ) {
            return new WP_Error( 'rest_invalid_end_date', __( 'Invalid Leave End Date.' ), array( 'status' => 404 ) );
        }

        $request_id = erp_hr_leave_insert_request(
            array(
                'user_id'      => $request['id'],
                'leave_policy' => $request['policy_id'],
                'start_date'   => $request['start_date'],
                'end_date'     => $request['end_date'],
                'reason'       => $request['reason'],
                'status'       => 0
            )
        );

        $response = rest_ensure_response( $request_id );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $request_id ) ) );

        return $response;
    }

    /**
     * Get all notes of a single employee
     *
     * @since 1.2.9
     *
     * @param \WP_REST_Request $request
     *
     * @return array
     */
    public function get_notes( $request ) {
        $args = array(
            'total'  => isset( $request['perpage'] ) ? $request['perpage'] : 20,
            'offset' => isset( $request['offset'] ) ? $request['offset'] : 0,
        );

        $id       = (int) $request['id'];
        $employee = new Employee( $id );
        if ( ! $employee ) {
            return new WP_Error( 'rest_invalid_employee_id', __( 'Invalid Employee id.' ), array( 'status' => 404 ) );
        }

        $notes           = $employee->get_notes( $args['total'], $args['offset'] );
        $formatted_items = [];

        if ( ! empty( $notes ) ) {
            foreach ( $notes as $note ) {
                $data              = $this->prepare_note_for_response( $note, $request );
                $formatted_items[] = $this->prepare_response_for_collection( $data );
            }
        }

        $response = rest_ensure_response( $formatted_items );
        $response = $this->format_collection_response( $response, $request, count( $notes ) );

        return $response;
    }

    /**
     * Create a note for employee
     *
     * @since 1.2.9
     *
     * @param $request
     *
     * @return array|mixed|WP_Error|WP_REST_Response
     */
    public function create_note( $request ) {
        $id       = (int) $request['id'];
        $employee = new Employee( $id );
        if ( ! $employee ) {
            return new WP_Error( 'rest_invalid_employee_id', __( 'Invalid Employee id.' ), array( 'status' => 404 ) );
        }
        $note = $employee->add_note( $request['note'], null, true );

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_note_for_response( $note, $request );

        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $id ) ) );

        return $response;
    }

    /**
     * Delete a note
     *
     * @since 1.2.9
     *
     * @param $request
     *
     * @return WP_REST_Response
     */
    public function delete_note( $request ) {
        $id = (int) $request['note_id'];

        Employee_Note::find( $id )->delete();

        return new WP_REST_Response( true, 204 );
    }

    /**
     * Get all performance of a single employee
     *
     * @since 1.2.9
     *
     * @param $request
     *
     * @return mixed|object|WP_Error|WP_REST_Response
     */
    public function get_performances( $request ) {
        $id       = (int) $request['id'];
        $employee = new Employee( $id );
        if ( ! $employee ) {
            return new WP_Error( 'rest_invalid_employee_id', __( 'Invalid Employee id.' ), array( 'status' => 404 ) );
        }

        $performances_collection = $employee->get_performance();
        $formatted_items         = [];

        foreach ( $performances_collection as $type => $performances ) {
            if ( ! empty( $performances ) ) {
                $group = [];
                foreach ( $performances as $performance ) {
                    $data    = $this->prepare_performance_for_response( (array) $performance, $request );
                    $group[] = $this->prepare_response_for_collection( $data );
                }
                $formatted_items[ $type ] = $group;
            }
        }

        $response = rest_ensure_response( $formatted_items );
        $response = $this->format_collection_response( $response, $request, count( $formatted_items ) );

        return $response;
    }

    /**
     * Create a performance
     *
     * @param \WP_REST_Request $request
     *
     * @return WP_Error|\WP_REST_Request
     */
    public function create_performance( $request ) {
        $employee_id = (int) trim( $request['id'] );
        $employee    = new Employee( $employee_id );
        if ( ! $employee ) {
            return new WP_Error( 'rest_invalid_employee_id', __( 'Invalid Employee id.' ), array( 'status' => 404 ) );
        }
        if ( empty( $request['type'] ) ) {
            return new WP_Error( 'rest_performance_required_fields', __( 'Review type is missing' ), array( 'status' => 400 ) );
        }
        if ( ! in_array( $request['type'], [ 'reviews', 'comments', 'goals' ] ) ) {
            return new WP_Error( 'rest_performance_invalid_type', __( 'Invalid review type' ), array( 'status' => 400 ) );
        }

        $type     = sanitize_key( $request['type'] );
        $requires = array();
        $fields   = array();

        if ( $type == 'reviews' ) {
            $performance_date = ( ! empty( $request['performance_date'] ) ) ? current_time( 'mysql' ) : $request['performance_date'];
            $reporting_to     = ( ! empty( $request['reporting_to'] ) ) ? intval( $request['reporting_to'] ) : 0;
            $job_knowledge    = ( ! empty( $request['job_knowledge'] ) ) ? intval( $request['job_knowledge'] ) : 0;
            $work_quality     = ( ! empty( $request['work_quality'] ) ) ? intval( $request['work_quality'] ) : 0;
            $attendance       = ( ! empty( $request['attendance'] ) ) ? intval( $request['attendance'] ) : 0;
            $communication    = ( ! empty( $request['communication'] ) ) ? intval( $request['communication'] ) : 0;
            $dependablity     = ( ! empty( $request['dependablity'] ) ) ? intval( $request['dependablity'] ) : 0;

            // some basic validations
            $requires = [
                'performance_date' => __( 'Review Date', 'erp' ),
                'reporting_to'     => __( 'Reporting To', 'erp' ),
                'job_knowledge'    => __( 'Job Knowledge', 'erp' ),
                'work_quality'     => __( 'Work Quality', 'erp' ),
                'attendance'       => __( 'Attendance', 'erp' ),
                'communication'    => __( 'Communication', 'erp' ),
                'dependablity'     => __( 'Dependability', 'erp' ),
            ];

            $fields = [
                'employee_id'      => $employee_id,
                'reporting_to'     => $reporting_to,
                'job_knowledge'    => $job_knowledge,
                'work_quality'     => $work_quality,
                'attendance'       => $attendance,
                'communication'    => $communication,
                'dependablity'     => $dependablity,
                'type'             => $type,
                'performance_date' => $performance_date
            ];
        }

        if ( $type && $type == 'comments' ) {

            $employee_id      = isset( $request['employee_id'] ) ? intval( $request['employee_id'] ) : 0;
            $performance_date = ( empty( $request['performance_date'] ) ) ? current_time( 'mysql' ) : $request['performance_date'];

            // some basic validations
            $requires = [
                'performance_date' => __( 'Reference Date', 'erp' ),
                'reviewer'         => __( 'Reviewer', 'erp' ),
                'comments'         => __( 'Comments', 'erp' ),
            ];

            $fields = [
                'employee_id'      => $employee_id,
                'reviewer'         => $request['reviewer'],
                'comments'         => $request['comments'],
                'type'             => $type,
                'performance_date' => $performance_date
            ];
        }

        if ( $type && $type == 'goals' ) {

            $completion_date       = ! empty( $request['completion_date'] ) ? current_time( 'mysql' ) : $request['completion_date'];
            $goal_description      = ! empty( $request['goal_description'] ) ? esc_textarea( $request['goal_description'] ) : '';
            $employee_assessment   = ! empty( $request['employee_assessment'] ) ? esc_textarea( $request['employee_assessment'] ) : '';
            $supervisor            = ! empty( $request['supervisor'] ) ? intval( $request['supervisor'] ) : 0;
            $supervisor_assessment = ! empty( $request['supervisor_assessment'] ) ? esc_textarea( $request['supervisor_assessment'] ) : '';
            $performance_date      = ! empty( $request['performance_date'] ) ? current_time( 'mysql' ) : $request['performance_date'];

            // some basic validations
            $requires = [
                'performance_date' => __( 'Reference Date', 'erp' ),
                'completion_date'  => __( 'Completion Date', 'erp' ),
                'supervisor'       => __( 'Supervisor', 'erp' ),
            ];

            $fields = [
                'completion_date'       => $completion_date,
                'goal_description'      => $goal_description,
                'employee_assessment'   => $employee_assessment,
                'supervisor'            => $supervisor,
                'supervisor_assessment' => $supervisor_assessment,
                'type'                  => $type,
                'performance_date'      => $performance_date
            ];
        }

        //global fields
        $fields['employee_id'] = $employee_id;

        foreach ( $requires as $field => $label ) {
            if ( empty( $fields[ $field ] ) ) {
                return new WP_Error( 'rest_performance_required_fields', sprintf( __( '%s is required', 'erp' ), $label ), array( 'status' => 400 ) );
            }
        }

        $performance = Performance::create( $fields );
        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_performance_for_response( $performance->toArray(), $request );

        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $employee_id ) ) );

        return $response;
    }

    /**
     * Delete performance
     *
     * @since 1.2.9
     *
     * @param $request
     *
     * @return WP_REST_Response
     */
    public function delete_performance( $request ) {
        $id = (int) $request['performance_id'];
        Performance::find( $id )->delete();

        return new WP_REST_Response( true, 204 );
    }

    /**
     * Get roles of an employee
     *
     * @since 1.2.9
     *
     * @param $request
     *
     * @return mixed|WP_Error|WP_REST_Response
     */
    public function get_roles( $request ) {
        $employee_id = (int) trim( $request['id'] );
        $employee    = new Employee( $employee_id );
        if ( ! $employee ) {
            return new WP_Error( 'rest_invalid_employee_id', __( 'Invalid Employee id.' ), array( 'status' => 404 ) );
        }
        $response = rest_ensure_response( $employee->get_roles() );

        return $response;
    }

    /**
     * Update employee roles
     * accepts associative array eg. ['erp_hr_manager' => true, 'erp_crm_manager' => false ]
     *
     * @since 1.2.9
     *
     * @param $request
     *
     * @return array|mixed|WP_Error|WP_REST_Response
     */
    public function update_role( $request ) {
        $hr_manager_role = erp_hr_get_manager_role();
        if ( ! current_user_can( $hr_manager_role ) ) {
            return new WP_Error( 'rest_invalid_user_permission', __( 'User do not have permission for the action.' ), array( 'status' => 404 ) );
        }
        $employee_id = (int) trim( $request['id'] );
        $employee    = new Employee( $employee_id );
        if ( ! $employee ) {
            return new WP_Error( 'rest_invalid_employee_id', __( 'Invalid Employee id.' ), array( 'status' => 404 ) );
        }
        if ( ! is_array( $request['roles'] ) || empty( $request['roles'] ) ) {
            return new WP_Error( 'rest_performance_invalid_permission_type', __( 'Invalid role format' ), array( 'status' => 400 ) );
        }

        $roles = $employee->update_role( $request['roles'] )->get_roles();
        $request->set_param( 'context', 'edit' );
        $response = rest_ensure_response( $roles );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $employee_id ) ) );

        return $response;
    }

    /**
     * Prepare performance for response
     *
     * @since  1.2.9
     *
     * @param  array            $performance
     * @param  \WP_REST_Request $request
     *
     * @return array
     */
    protected function prepare_performance_for_response( $performance, $request ) {
        $formatted                     = array();
        $formatted['id']               = $performance['id'];
        $formatted['type']             = $performance['type'];
        $formatted['performance_date'] = $performance['performance_date'];
        if ( $performance['type'] == 'reviews' ) {
            $formatted['job_knowledge']          = erp_performance_rating( $performance['job_knowledge'] ? $performance['job_knowledge'] : '-' );
            $formatted['work_quality']           = erp_performance_rating( $performance['work_quality'] ? $performance['work_quality'] : '-' );
            $formatted['attendance']             = erp_performance_rating( $performance['attendance'] ? $performance['attendance'] : '-' );
            $formatted['dependablity']           = erp_performance_rating( $performance['dependablity'] ? $performance['dependablity'] : '-' );
            $formatted['communication']          = erp_performance_rating( $performance['communication'] ? $performance['communication'] : '-' );
            $reporting_to_user                   = new \WeDevs\ERP\HRM\Employee( intval( $performance['reporting_to'] ) );
            $formatted['reporting_to']           = intval( $performance['reporting_to'] );
            $formatted['reporting_to_full_name'] = $reporting_to_user->get_full_name();
        } elseif ( $performance['type'] == 'comments' ) {
            $formatted['comments']           = $performance['comments'];
            $formatted['reviewer']           = intval( $performance['reviewer'] );
            $reviewer_user                   = new \WeDevs\ERP\HRM\Employee( intval( $performance['reviewer'] ) );
            $formatted['reviewer_full_name'] = $reviewer_user->get_full_name();
        } else {
            $formatted['completion_date']       = $performance['completion_date'];
            $formatted['goal_description']      = $performance['goal_description'];
            $formatted['employee_assessment']   = $performance['employee_assessment'];
            $formatted['supervisor']            = $performance['supervisor'];
            $formatted['supervisor_assessment'] = $performance['supervisor_assessment'];
            $supervisor_user                    = new \WeDevs\ERP\HRM\Employee( intval( $performance['supervisor'] ) );
            $formatted['supervisor_full_name']  = $supervisor_user->get_full_name();
        }

        return $formatted;
    }

    /**
     * Get employee histories
     *
     * @since 1.2.9
     *
     * @param $request
     *
     * @return mixed|object|WP_Error|WP_REST_Response
     */
    public function get_histories( $request ) {
        $id       = (int) $request['id'];
        $module   = ! empty( $request['module'] ) ? sanitize_key( $request['module'] ) : 0;
        $employee = new Employee( $id );
        if ( ! $employee ) {
            return new WP_Error( 'rest_invalid_employee_id', __( 'Invalid Employee id.' ), array( 'status' => 404 ) );
        }

        $histories = $employee->get_history();
        if ( ! empty( $module ) && isset( $histories[ $module ] ) ) {
            $histories = $histories[ $module ];
        }

        $response = rest_ensure_response( $histories );
        $response = $this->format_collection_response( $response, $request, count( $histories ) );

        return $response;
    }

    /**
     * Create employee history
     *
     * @since 1.2.9
     *
     * @param $request
     *
     * @return mixed|WP_Error|WP_REST_Response
     */
    public function create_history( \WP_REST_Request $request ) {

        global $wpdb;
        $id       = (int) $request['id'];
        $module   = ! empty( $request['module'] ) ? sanitize_key( $request['module'] ) : 0;
        $employee = new Employee( $id );
        if ( ! $employee ) {
            return new WP_Error( 'rest_invalid_employee_id', __( 'Invalid Employee id.' ), array( 'status' => 404 ) );
        }
        if ( empty( $module ) || ( ! in_array( $module, [ 'status', 'compensation', 'information' ] ) ) ) {
            return new WP_Error( 'rest_no_module_type', __( 'Invalid/No module type' ), array( 'status' => 404 ) );
        }

        $date = ( empty( $request['date'] ) ) ? current_time( 'mysql' ) : $request['date'];

        if ( $module == 'status' ) {
            $required = [
                'status' => __( 'Employment Status', 'erp' ),
            ];
            $fields   = [
                'status'  => ! empty( $request['status'] ) ? sanitize_key( $request['status'] ) : 0,
                'comment' => ! empty( $request['comment'] ) ? sanitize_textarea_field( $request['comment'] ) : '',
            ];

            $error = $this->check_required_fields( $required, $fields );
            if ( $error instanceof WP_Error ) {
                return $error;
            }

            $types = erp_hr_get_employee_types();
            if ( ! array_key_exists( $fields['status'], $types ) ) {
                return new WP_Error( 'rest_invalid_employee_status', __( 'Invalid employee type', 'erp' ), array( 'status' => 400 ) );
            }

            $employee->update_employment_status( $fields['status'], $date, $fields['comment'] );
            $history = $employee->get_history_by_id( $wpdb->insert_id );

            return rest_ensure_response( $history );

        } elseif ( $module == 'compensation' ) {
            $required = [
                'reason'   => __( 'Change Reason', 'erp' ),
                'pay_type' => __( 'Pay Type', 'erp' ),
                'pay_rate' => __( 'Pay Rate', 'erp' ),
            ];
            $fields   = [
                'comment'  => ! empty( $request['comment'] ) ? sanitize_textarea_field( $request['comment'] ) : '',
                'status'   => ! empty( $request['status'] ) ? sanitize_key( $request['status'] ) : 0,
                'reason'   => ! empty( $request['reason'] ) ? sanitize_key( $request['reason'] ) : 0,
                'pay_type' => ! empty( $request['pay_type'] ) ? sanitize_key( $request['pay_type'] ) : 0,
                'pay_rate' => ! empty( $request['pay_rate'] ) ? number_format( $request['pay_rate'], 2 ) : 0,
            ];

            $error = $this->check_required_fields( $required, $fields );
            if ( $error instanceof WP_Error ) {
                return $error;
            }

            if ( ! $fields['pay_rate'] ) {
                return new WP_Error( 'rest_invalid_pay_rate', __( 'Invalid pay rate', 'erp' ), array( 'status' => 400 ) );
            }

            $types   = erp_hr_get_pay_type();
            $reasons = erp_hr_get_pay_change_reasons();
            if ( ! array_key_exists( $fields['pay_type'], $types ) ) {
                return new WP_Error( 'rest_invalid_pay_type', __( 'Invalid pay type', 'erp' ), array( 'status' => 400 ) );
            }

            if ( ! array_key_exists( $fields['reason'], $reasons ) ) {
                return new WP_Error( 'rest_invalid_reason', __( 'Reason does not exists', 'erp' ), array( 'status' => 400 ) );
            }

            $employee->update_compensation( $fields['pay_rate'], $fields['pay_type'], $fields['reason'], $date, $fields['comment'] );
            $history = $employee->get_history_by_id( $wpdb->insert_id );

            return rest_ensure_response( $history );


        } else {
            $required = [
                'designation'  => __( 'Designation', 'erp' ),
                'department'   => __( 'Department', 'erp' ),
                'location'     => __( 'Location', 'erp' ),
                'reporting_to' => __( 'Reporting To', 'erp' ),
            ];

            $fields = [
                'comment'      => ! empty( $request['comment'] ) ? sanitize_textarea_field( $request['comment'] ) : '',
                'designation'  => ! empty( $request['designation'] ) ? intval( $request['designation'] ) : 0,
                'department'   => ! empty( $request['department'] ) ? intval( $request['department'] ) : 0,
                'location'     => ! empty( $request['location'] ) ? sanitize_key( $request['location'] ) : 0,
                'reporting_to' => ! empty( $request['reporting_to'] ) ? intval( $request['reporting_to'] ) : 0,
                'pay_rate'     => ! empty( $request['pay_rate'] ) ? number_format( $request['pay_rate'], 2 ) : 0,
            ];

            $error = $this->check_required_fields( $required, $fields );
            if ( $error instanceof WP_Error ) {
                return $error;
            }

            if ( ! Designation::find( $fields['designation'] ) ) {
                return new WP_Error( 'rest_invalid_designation', __( 'Invalid designation ID', 'erp' ), array( 'status' => 400 ) );
            }

            if ( ! Department::find( $fields['department'] ) ) {
                return new WP_Error( 'rest_invalid_department', __( 'Invalid department ID', 'erp' ), array( 'status' => 400 ) );
            }
            $reporting_user = \WeDevs\ERP\HRM\Models\Employee::where( 'user_id', $fields['reporting_to'] )->first();
            if ( ! $reporting_user ) {
                return new WP_Error( 'rest_invalid_reporting_to', __( 'Invalid Reporting to user id', 'erp' ), array( 'status' => 400 ) );
            }

            $employee->update_job_info( $fields['department'], $fields['designation'], $fields['reporting_to'], $fields['location'], $date );
            $history = $employee->get_history_by_id( $wpdb->insert_id );

            return rest_ensure_response( $history );
        }
    }

    /**
     * Delete a history
     *
     * @since 1.2.9
     *
     * @param $request
     *
     * @return \WP_Error|\WP_REST_Response
     * @throws \Exception
     */
    public function delete_history( $request ) {
        $id = (int) $request['id'];

        $employee = new Employee( $id );
        if ( ! $employee ) {
            return new WP_Error( 'rest_invalid_employee_id', __( 'Invalid Employee id.' ), array( 'status' => 404 ) );
        }

        $history_id = (int) $request['history_id'];
        if ( empty( $history_id ) ) {
            return new WP_Error( 'rest_invalid_history_id', __( 'Invalid history id received', 'erp' ), array( 'status' => 400 ) );
        }

        $employee->delete_history( $history_id );

        return new WP_REST_Response( true, 204 );
    }

    /**
     * Get all the events of a single user
     *
     * @since 1.2.9
     *
     * @param $request
     *
     * @return mixed|object|WP_Error|WP_REST_Response
     */
    public function get_events( $request ) {
        $user_id  = (int) $request['id'];
        $employee = new Employee( $user_id );
        if ( ! $employee ) {
            return new WP_Error( 'rest_invalid_employee_id', __( 'Invalid Employee id.' ), array( 'status' => 404 ) );
        }

        $leave_requests = erp_hr_get_calendar_leave_events( false, $user_id, false );
        $holidays       = erp_array_to_object( \WeDevs\ERP\HRM\Models\Leave_Holiday::all()->toArray() );
        $events         = [];
        $holiday_events = [];
        $event_data     = [];

        foreach ( $leave_requests as $key => $leave_request ) {
            //if status pending
            $policy      = erp_hr_leave_get_policy( $leave_request->policy_id );
            $event_label = $policy->name;
            if ( 2 == $leave_request->status ) {
                $policy      = erp_hr_leave_get_policy( $leave_request->policy_id );
                $event_label .= sprintf( ' ( %s ) ', __( 'Pending', 'erp' ) );
            }
            $events[] = array(
                'id'    => $leave_request->id,
                'title' => $event_label,
                'start' => $leave_request->start_date,
                'end'   => $leave_request->end_date,
                'url'   => erp_hr_url_single_employee( $leave_request->user_id, 'leave' ),
                'color' => $leave_request->color,
            );
        }

        foreach ( $holidays as $key => $holiday ) {
            $holiday_events[] = [
                'id'      => $holiday->id,
                'title'   => $holiday->title,
                'start'   => $holiday->start,
                'end'     => $holiday->end,
                'color'   => '#FF5354',
                'img'     => '',
                'holiday' => true
            ];
        }

        $event_data = array_merge( $events, $holiday_events );

        $response = rest_ensure_response( $event_data );
        $response = $this->format_collection_response( $response, $request, count( $event_data ) );

        return $response;
    }

    /**
     * Terminate the employee
     *
     * @since 1.2.9
     *
     * @param $request
     *
     * @return mixed|\WP_Error|\WP_REST_Response
     */
    public function create_terminate( $request ) {
        $user_id  = (int) $request['id'];
        $employee = new Employee( $user_id );
        if ( ! $employee ) {
            return new WP_Error( 'rest_invalid_employee_id', __( 'Invalid Employee id.' ), array( 'status' => 404 ) );
        }

        $employee_id         = isset( $request['employee_id'] ) ? intval( $request['employee_id'] ) : 0;
        $terminate_date      = ( empty( $request['terminate_date'] ) ) ? current_time( 'mysql' ) : $request['terminate_date'];
        $termination_type    = isset( $request['termination_type'] ) ? $request['termination_type'] : '';
        $termination_reason  = isset( $request['termination_reason'] ) ? $request['termination_reason'] : '';
        $eligible_for_rehire = isset( $request['eligible_for_rehire'] ) ? $request['eligible_for_rehire'] : '';

        $fields = [
            'employee_id'         => $employee_id,
            'terminate_date'      => $terminate_date,
            'termination_type'    => $termination_type,
            'termination_reason'  => $termination_reason,
            'eligible_for_rehire' => $eligible_for_rehire
        ];

        $result = $employee->terminate( $fields );

        if ( is_wp_error( $result ) ) {
            return new WP_Error( 'rest_insufficient_data', $result->get_error_messages(), array( 'status' => 401 ) );
        }

        $request->set_param( 'context', 'edit' );
        $response = rest_ensure_response( true );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $user_id ) ) );

        return $response;

    }

    /**
     * Get announcement of an employee
     *
     * @since 1.2.9
     *
     * @param $request
     *
     * @return mixed|object|\WP_Error|\WP_REST_Response
     */
    public function get_announcements( $request ) {
        $user_id  = (int) $request['id'];
        $employee = new Employee( $user_id );
        if ( ! $employee ) {
            return new WP_Error( 'rest_invalid_employee_id', __( 'Invalid Employee id.' ), array( 'status' => 404 ) );
        }
        $announcements = erp_hr_employee_dashboard_announcement( $user_id );

        $formatted_items = [];
        foreach ( $announcements as $announcement ) {

            $author          = new Employee( $announcement->post_author );
            $item            = [];
            $item['id']      = $announcement->ID;
            $item['author']  = $author->get_full_name();
            $item['date']    = $announcement->post_date;
            $item['status']  = $announcement->status;
            $item['title']   = $announcement->post_title;
            $item['content'] = $announcement->post_content;

            $formatted_items[] = $item;
        }

        $response = rest_ensure_response( $formatted_items );
        $response = $this->format_collection_response( $response, $request, count( $formatted_items ) );

        return $response;
    }

    /**
     * Prepare note for response
     *
     * @since  1.2.9
     *
     * @param  array            $note
     * @param  \WP_REST_Request $request
     *
     * @return array
     */
    protected function prepare_note_for_response( $note, $request ) {
        $commenter_user = get_user_by( 'ID', $note['comment_by'] );
        $user           = get_user_by( 'ID', $note['user_id'] );

        $note['comment_by_display_name'] = $commenter_user->display_name;
        $note['comment_by_avatar_url']   = get_avatar_url( $note['comment_by'] );

        $note['comment_by_display_name'] = $user->display_name;
        $note['comment_by_avatar_url']   = get_avatar_url( $note['user_id'] );

        return $note;
    }

    /**
     * Prepare a single dependent for create or update
     *
     * @param \WP_REST_Request $request Request object.
     *
     * @return array $prepared_item
     */
    protected function prepare_dependent_for_database( $request ) {
        $prepared_item = [];

        // required arguments.
        if ( isset( $request['name'] ) ) {
            $prepared_item['name'] = $request['name'];
        }

        if ( isset( $request['relation'] ) ) {
            $prepared_item['relation'] = $request['relation'];
        }

        if ( isset( $request['date_of_birth'] ) ) {
            $prepared_item['dob'] = $request['date_of_birth'];
        }

        if ( isset( $request['employee_id'] ) ) {
            $prepared_item['employee_id'] = absint( $request['employee_id'] );
        }

        // optional arguments.
        if ( isset( $request['id'] ) ) {
            $prepared_item['id'] = absint( $request['id'] );
        }

        return $prepared_item;
    }

    /**
     * Prepare a single dependent output for response
     *
     * @param object           $item
     * @param \WP_REST_Request $request Request object.
     * @param array            $additional_fields (optional)
     *
     * @return WP_REST_Response $response Response data.
     */
    public function prepare_dependent_for_response( $item, $request, $additional_fields = [] ) {
        $data = [
            'id'            => (int) $item->id,
            'name'          => $item->name,
            'relation'      => $item->relation,
            'date_of_birth' => $item->dob,
        ];

        $data = array_merge( $data, $additional_fields );

        // Wrap the data in a response object
        $response = rest_ensure_response( $data );

        return $response;
    }

    /**
     * Prepare a single leave output for response
     *
     * @param object           $item
     * @param \WP_REST_Request $request Request object.
     * @param array            $additional_fields (optional)
     *
     * @return WP_REST_Response $response Response data.
     */
    public function prepare_leave_for_response( $item, $request, $additional_fields = [] ) {
        $data = [
            'id'           => (int) $item->id,
            'user_id'      => $item->user_id,
            'display_name' => $item->display_name,
            'policy_id'    => $item->policy_id,
            'policy_name'  => $item->policy_name,
            'status'       => $item->status,
            'reason'       => $item->reason,
            'comments'     => $item->comments,
            'created_on'   => $item->created_on,
            'days'         => $item->days,
            'start_date'   => $item->start_date,
            'end_date'     => $item->end_date,
        ];

        $data = array_merge( $data, $additional_fields );

        // Wrap the data in a response object
        $response = rest_ensure_response( $data );

        return $response;
    }

    /**
     * Checks required fields in fields
     *
     * @since 1.2.9
     *
     * @param array $requires
     * @param array $fields
     *
     * @return bool|WP_Error
     */
    protected function check_required_fields( array $requires, array $fields ) {
        foreach ( $requires as $field => $label ) {
            if ( empty( $fields[ $field ] ) ) {
                return new WP_Error( 'rest_required_fields', sprintf( __( '%s is required', 'erp' ), $label ), array( 'status' => 400 ) );
            }
        }

        return true;
    }

    /**
     * Get the User's schema, conforming to JSON Schema
     *
     * @return array
     */
    public function get_item_schema() {
        $schema = [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'employee',
            'type'       => 'object',
            'properties' => [
                'id'            => [
                    'description' => __( 'Unique identifier for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'first_name'    => [
                    'description' => __( 'First name for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'middle_name'   => [
                    'description' => __( 'Middle name for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'last_name'     => [
                    'description' => __( 'Last name for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'email'         => [
                    'description' => __( 'The email address for the resource.' ),
                    'type'        => 'string',
                    'format'      => 'email',
                    'context'     => [ 'edit' ],
                    'required'    => true,
                ],
                'location'      => [
                    'description' => __( 'Location for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'hiring_source' => [
                    'description' => __( 'Hiring source for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'hiring_date'   => [
                    'description' => __( 'Hiring date for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'date_of_birth' => [
                    'description' => __( 'Date of birth for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'pay_rate'      => [
                    'description' => __( 'Pay rate for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'pay_type'      => [
                    'description' => __( 'Pay type for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'type'          => [
                    'description' => __( 'Type for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'status'        => [
                    'description' => __( 'Status for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'phone'         => [
                    'description' => __( 'Phone for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'work_phone'    => [
                    'description' => __( 'Work phone for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'mobile'        => [
                    'description' => __( 'Mobile for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'other'         => [
                    'description' => __( 'Other for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'user_url'      => [
                    'description' => __( 'Website of the resource.' ),
                    'type'        => 'string',
                    'format'      => 'uri',
                    'context'     => [ 'embed', 'view', 'edit' ],
                ],
                'street_1'      => [
                    'description' => __( 'Street 1 of the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'street_2'      => [
                    'description' => __( 'Street 1 of the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'city'          => [
                    'description' => __( 'City of the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'state'         => [
                    'description' => __( 'State of the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'postal_code'   => [
                    'description' => __( 'Postal Code of the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'country'       => [
                    'description' => __( 'Country of the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ],
        ];

        return $schema;
    }
}