<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\CategoryModel;
use App\Models\BlogModel;

class ApiController extends ResourceController
{
    private $db;
    public function __construct(){

        $this->db = db_connect();

    }

    // POST
    public function createCategory(){

        $rules = [
            "name" => "required|is_unique[categories.name]",
        ];

        if(!$this->validate($rules)){
            $response = [
                "status" => 200,
                "message" => $this->validator->getErrors(),
                "error" => true,
                "data" => []
            ];
        } else {

            $category_obj = new CategoryModel();

            $data = [
                "name" => $this->request->getVar("name"),
                "status" => $this->request->getVar("status"),
            ];

            if($category_obj->insert($data)){
                $response = [
                    "status" => 200,
                    "message" => "Created New Category",
                    "error" => false,
                    "data" => $data
                ];
            } else {
                $response = [
                    "status" => 500,
                    "message" => "Failed to create new category",
                    "error" => true,
                    "data" => []
                ];
            }

        }

        return $this->respondCreated($response);

    }

    // GET
    public function listCategory(){


        $builder = $this->db->table("categories");

        $builder->select("*");
        $data = $builder->get()->getResult();

        $response = [
            "status" => 200,
            "message" => "List Categories",
            "error" => false,
            "data" => $data
        ];

        return $this->respondCreated($response);

    }

    /////////////////

    // POST
    public function createBlog(){

        $rules = [
            "category_id" => "required",
            "title" => "required"
        ];

        if(!$this->validate($rules)){

            $response = [
                "status" => 500,
                "message" => $this->validator->getErrors(),
                "error" => true,
                "data" => []
            ];

        } else {

            $category_obj = new CategoryModel();

            $is_exists = $category_obj->find($this->request->getVar("category_id"));

            if(!empty($is_exists)){
                // category exists
                $blog_obj = new BlogModel();

                $data = [
                    "category_id" => $this->request->getVar("category_id"),
                    "title" => $this->request->getVar("title"),
                    "content" => $this->request->getVar("content")
                ];


                if($blog_obj->insert($data)){
                    // blog created

                    $response = [
                        "status" => 200,
                        "message" => "Blog created",
                        "error" => false,
                        "data" => $data
                    ];

                } else {

                    $response = [
                        "status" => 500,
                        "message" => "Failed to create blog",
                        "error" => true,
                        "data" => []
                    ];

                }

            } else {
                // category doesnot exists

                $response = [
                    "status" => 404,
                    "message" => "Category not found",
                    "error" => true,
                    "data" => []
                ];
            }

        }

        return $this->respondCreated($response);

    }

    // GET
    public function listBlogs(){

        $builder = $this->db->table("blogs");

        $builder->select("blogs.*, categories.name as category_name");
        $builder->join("categories", "categories.id = blogs.category_id");
        $data = $builder->get()->getResult();

        $response = [
            "status" => 200,
            "message" => "List blogs",
            "error" => false,
            "data" => $data
        ];

        return $this->respondCreated($response);
    }

    // GET
    public function singleBlogDetail($blog_id){
        $builder = $this->db->table("blogs as posts");

        $builder->select("posts.*, categories.name as category_name");
        $builder->join("categories", "posts.category_id = categories.id");
        $builder->where("posts.id", $blog_id);
        $data = $builder->get()->getRow();

        $response = [
            "status" => 200,
            "message" => "Single blog detail",
            "error" => false,
            "data" => $data
        ];

        return $this->respondCreated($response);
    }


    // POST -> PUT
    public function updateBlog($blog_id){
            $blog_obj = new BlogModel();
    
            $blog_exists = $blog_obj->find($blog_id);
    
            if(!empty($blog_exists)){
                // blog exists
                $rules = [
                    "category_id" => "required",
                ];
    
                if(!$this->validate($rules)){
                    // errors
                    $response = [
                        "status" => 500,
                        "message" => $this->validator->getErrors(),
                        "error" => true,
                        "data" => []
                    ];
                }else{
                    // no error
                    $category_obj = new CategoryModel();
    
                    $category_exists = $category_obj->find($this->request->getVar("category_id"));
    
                    if(!empty($category_exists)){
                        // category exists
    
                        $data = [
                            "category_id" => $this->request->getVar("category_id"),
                            "title" => $this->request->getVar("title"),
                            "content" => $this->request->getVar("content")
                        ];
    
                        $blog_obj->update($blog_id, $data);
    
                        $response = [
                            "status" => 200,
                            "message" => "Category updated successfully",
                            "error" => false,
                            "data" => []
                        ];
    
                    }else{
                        // category doesnot exists
                        $response = [
                            "status" => 404,
                            "message" => "Category not found",
                            "error" => true,
                            "data" => []
                        ];
                    }
                }
            }else{
                // blog doesnot exists
                $response = [
                    "status" => 404,
                    "message" => "Blog not found",
                    "error" => true,
                    "data" => []
                ];
            }
    
            return $this->respondCreated($response);
        }

    // DELETE
    public function deleteBlog($blog_id){
        $blog_obj = new BlogModel();

		$blog_exists = $blog_obj->find($blog_id);

		if(!empty($blog_exists)){
			
			$blog_obj->delete($blog_id);

			$response = [
				"status" => 200,
				"message" => "Blog deleted successfully",
				"error" => false,
				"data" => []
			];
		}else{
			// blog doesnot exists
			$response = [
				"status" => 404,
				"message" => "Blog not found",
				"error" => true,
				"data" => []
			];
		}

		return $this->respondCreated($response);
	}

}
